<?php
namespace App\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

trait EncryptsAttributes {

    public function attributesToArray() {
        $attributes = parent::attributesToArray();
        foreach ($this->getEncrypts() as $key) {
            if (array_key_exists($key, $attributes)) {
                try {
                    $attributes[$key] = decrypt($attributes[$key]);
                } catch (DecryptException $e) {
                    // handle decryption failure
                    if (method_exists($this, 'handleEncryptionError')) {
                        $this->handleEncryptionError($key, $e);
                        $this->refresh();
                        // try again after handling
                        return $this->attributesToArray();
                    }

                    $attributes[$key] = null;
                    \Log::error("Failed to decrypt {$key}", [
                        'model' => get_class($this),
                        'id' => $this->{$this->getKeyName()},
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        return $attributes;
    }

    public function getAttributeValue($key)
    {
        if  (in_array($key, $this->getEncrypts())) {
            try {
                return decrypt($this->attributes[$key]);
            } catch (DecryptException $e) {
                // handle decryption failure
                if (method_exists($this, 'handleEncryptionError')) {
                    $this->handleEncryptionError($key, $e);
                    $this->refresh();
                    // try again after handling
                    return $this->getAttributeValue($key);
                }

                \Log::error("Failed to decrypt {$key}", [
                    'model' => get_class($this),
                    'id' => $this->{$this->getKeyName()},
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        }
        return parent::getAttributeValue($key);
    }

    public function setAttribute($key, $value) {
        if(in_array($key, $this->getEncrypts())) {
            $this->attributes[$key] = encrypt($value);
        } else {
            parent::setAttribute($key, $value);
        }
        return $this;
    }

    protected function getEncrypts() {
        return property_exists($this, 'encrypts') ? $this->encrypts : [];
    }

}
