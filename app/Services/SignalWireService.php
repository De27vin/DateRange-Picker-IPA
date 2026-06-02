<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SignalWireService
{
    private Client $client;
    private string $space;
    private string $projectId;
    private string $apiToken;
    private string $baseUrl;
    private array $errorNotificationEmails;

    public function __construct()
    {
        $this->client = new Client();
        $this->space = config('services.signalwire.space');
        $this->projectId = config('services.signalwire.project_id');
        $this->apiToken = config('services.signalwire.api_token');
        $this->baseUrl = "https://{$this->space}/api/relay/rest/endpoints/sip";

        $emails = config('services.signalwire.error_notification_emails', '');
        $this->errorNotificationEmails = array_filter(array_map('trim', explode(',', $emails)));
    }

    public function createSipEndpoint(User $user, string $plainPassword): bool
    {
        try {
            $primaryEmail = $user->getPrimaryEmail();

            if (empty($primaryEmail)) {
                Log::error('Cannot create SIP endpoint: user has no primary email', [
                    'user_id' => $user->user_id
                ]);
                return false;
            }

            $sipUsername = $this->generateSipUsername($user);
            $sipPassword = $this->generateSipPassword($plainPassword);

            Log::info('Creating SIP endpoint', [
                'user_id' => $user->user_id,
                'primary_email' => $primaryEmail,
                'sip_username' => $sipUsername
            ]);

            $existingEndpointId = $this->findEndpointIdByUsername($sipUsername);

            if ($existingEndpointId) {
                Log::warning('SIP endpoint already exists, updating instead of creating', [
                    'user_id' => $user->user_id,
                    'endpoint_id' => $existingEndpointId,
                    'username' => $sipUsername
                ]);

                return $this->updateSipEndpointById($existingEndpointId, $user, $plainPassword);
            }

            $body = [
                'username' => $sipUsername,
                'password' => $sipPassword,
                'caller_id' => $user->name,
                'codecs' => [
                    'OPUS',
                    'G722',
                    'PCMU',
                    'PCMA',
                    'VP8',
                    'H264'
                ],
                'ciphers' => [
                    'AEAD_AES_256_GCM_8',
                    'AES_256_CM_HMAC_SHA1_80',
                    'AES_CM_128_HMAC_SHA1_80',
                    'AES_256_CM_HMAC_SHA1_32',
                    'AES_CM_128_HMAC_SHA1_32'
                ],
                'encryption' => 'required'
            ];

            $response = $this->client->post($this->baseUrl, [
                'headers' => $this->getAuthHeaders(),
                'json' => $body
            ]);

            $data = json_decode($response->getBody(), true);

            Log::info('SignalWire SIP endpoint created successfully', [
                'user_id' => $user->user_id,
                'username' => $sipUsername,
                'endpoint_id' => $data['id'] ?? null
            ]);

            return true;

        } catch (ClientException $e) {
            $this->handleApiError('create', $e, $user);
            return false;
        } catch (\Throwable $e) {
            $this->handleGeneralError('create', $e, $user);
            return false;
        }
    }

    private function updateSipEndpointById(string $endpointId, User $user, ?string $plainPassword = null): bool
    {
        try {
            $body = [
                'caller_id' => $user->name,
            ];

            if ($plainPassword) {
                $body['password'] = $this->generateSipPassword($plainPassword);
            }

            Log::info('Updating SIP endpoint by ID', [
                'user_id' => $user->user_id,
                'endpoint_id' => $endpointId,
                'updating_password' => $plainPassword !== null
            ]);

            $response = $this->client->put("{$this->baseUrl}/{$endpointId}", [
                'headers' => $this->getAuthHeaders(),
                'json' => $body
            ]);

            Log::info('SignalWire SIP endpoint updated successfully', [
                'user_id' => $user->user_id,
                'endpoint_id' => $endpointId
            ]);

            return true;

        } catch (ClientException $e) {
            $this->handleApiError('update', $e, $user);
            return false;
        } catch (\Throwable $e) {
            $this->handleGeneralError('update', $e, $user);
            return false;
        }
    }

    public function updateSipEndpoint(User $user, ?string $plainPassword = null): bool
    {
        try {
            $primaryEmail = $user->getPrimaryEmail();
            $sipUsername = $this->generateSipUsername($user);

            Log::info('Updating SIP endpoint', [
                'user_id' => $user->user_id,
                'primary_email' => $primaryEmail,
                'sip_username' => $sipUsername,
                'will_update_password' => $plainPassword !== null
            ]);

            $endpointId = $this->findEndpointIdByUsername($sipUsername);

            if (!$endpointId) {
                Log::warning('SIP endpoint not found for update', [
                    'user_id' => $user->user_id,
                    'username' => $sipUsername
                ]);

                // If password provided, try to create endpoint
                if ($plainPassword) {
                    Log::info('Endpoint not found, will try to create instead');
                    return $this->createSipEndpoint($user, $plainPassword);
                }

                return false;
            }

            Log::info('Found endpoint to update', [
                'user_id' => $user->user_id,
                'endpoint_id' => $endpointId
            ]);

            $body = [
                'caller_id' => $user->name,
            ];

            // Update password if provided (e.g., after password change)
            if ($plainPassword) {
                $body['password'] = $this->generateSipPassword($plainPassword);
            }

            $response = $this->client->put("{$this->baseUrl}/{$endpointId}", [
                'headers' => $this->getAuthHeaders(),
                'json' => $body
            ]);

            Log::info('SignalWire SIP endpoint updated successfully', [
                'user_id' => $user->user_id,
                'endpoint_id' => $endpointId,
                'password_updated' => $plainPassword !== null
            ]);

            return true;

        } catch (ClientException $e) {
            $this->handleApiError('update', $e, $user);
            return false;
        } catch (\Throwable $e) {
            $this->handleGeneralError('update', $e, $user);
            return false;
        }
    }

    public function deleteSipEndpointByUsername(?string $sipUsername): bool
    {
        if (!$sipUsername) {
            Log::warning('Cannot delete SIP endpoint: no username provided');
            return false;
        }

        Log::info('Deleting SIP endpoint by username', [
            'sip_username' => $sipUsername
        ]);

        try {
            $endpointId = $this->findEndpointIdByUsername($sipUsername);

            if (!$endpointId) {
                Log::info('SIP endpoint not found for deletion, considering it already deleted', [
                    'username' => $sipUsername
                ]);
                return true;
            }

            Log::info('Found endpoint to delete', [
                'username' => $sipUsername,
                'endpoint_id' => $endpointId
            ]);

            $response = $this->client->delete("{$this->baseUrl}/{$endpointId}", [
                'headers' => $this->getAuthHeaders()
            ]);

            Log::info('SignalWire SIP endpoint deleted by username', [
                'username' => $sipUsername,
                'endpoint_id' => $endpointId
            ]);

            return true;

        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                Log::info('SIP endpoint already deleted (404)', [
                    'username' => $sipUsername
                ]);
                return true;
            }
            Log::error('SignalWire API error during deletion by username', [
                'username' => $sipUsername,
                'error' => $e->getMessage()
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('SignalWire service error during deletion by username', [
                'username' => $sipUsername,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }


    public function deleteSipEndpoint(User $user): bool
    {
        $sipUsername = $this->generateSipUsername($user);
        return $this->deleteSipEndpointByUsername($sipUsername);
    }

    private function findEndpointIdByUsername(string $sipUsername): ?string
    {
        try {
            $pageNumber = 0;
            $pageSize = 50;

            do {
                $url = $this->baseUrl . "?page_number={$pageNumber}&page_size={$pageSize}";

                $response = $this->client->get($url, [
                    'headers' => $this->getAuthHeaders()
                ]);

                $responseData = json_decode($response->getBody(), true);
                // Response structure: {"data": [...endpoints...], "links": {...}}
                $endpoints = $responseData['data'] ?? [];
                $links = $responseData['links'] ?? [];

                // Search for our endpoint in this page
                foreach ($endpoints as $endpoint) {
                    if (isset($endpoint['username']) && $endpoint['username'] === $sipUsername) {
                        return $endpoint['id'];
                    }
                }

                // Check if there's a next page
                $hasNextPage = isset($links['next']) && !empty($links['next']);
                $pageNumber++;

                // Safety limit: stop after 20 pages (1000 endpoints)
                if ($pageNumber > 20) {
                    Log::warning('Stopped SignalWire pagination after 20 pages', [
                        'username' => $sipUsername
                    ]);
                    break;
                }

            } while ($hasNextPage);

            return null;

        } catch (\Throwable $e) {
            Log::error('Failed to list SignalWire endpoints', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function generateSipUsername(User $user): string
    {
        $primaryEmail = $user->getPrimaryEmail();
        return hash('sha256', $primaryEmail);
    }

    private function generateSipPassword(string $plainPassword): string
    {
        return hash('sha256', $plainPassword);
    }

    private function getAuthHeaders(): array
    {
        $authString = base64_encode($this->projectId . ':' . $this->apiToken);

        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . $authString
        ];
    }

    private function handleApiError(string $operation, ClientException $e, User $user): void
    {
        $responseBody = $e->hasResponse() ? json_decode($e->getResponse()->getBody(), true) : null;

        $errorData = [
            'operation' => $operation,
            'user_id' => $user->user_id,
            'user_email' => $user->getPrimaryEmail(),
            'status_code' => $e->getCode(),
            'error_message' => $e->getMessage(),
            'api_response' => $responseBody
        ];

        Log::error('SignalWire API error', $errorData);
        $this->sendErrorNotification($errorData);
    }

    private function handleGeneralError(string $operation, \Throwable $e, User $user): void
    {
        $errorData = [
            'operation' => $operation,
            'user_id' => $user->user_id,
            'user_email' => $user->getPrimaryEmail(),
            'error_type' => get_class($e),
            'error_message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];

        Log::error('SignalWire service error', $errorData);
        $this->sendErrorNotification($errorData);
    }

    private function sendErrorNotification(array $errorData): void
    {
        if (empty($this->errorNotificationEmails)) {
            return;
        }

        try {
            Mail::send('emails.signalwire-error', ['error' => $errorData], function ($message) use ($errorData) {
                $message->to($this->errorNotificationEmails)
                    ->subject('SignalWire SIP Endpoint Error - ' . ucfirst($errorData['operation']));
            });
        } catch (\Throwable $e) {
            Log::error('Failed to send SignalWire error notification email', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
