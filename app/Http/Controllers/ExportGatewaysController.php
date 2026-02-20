<?php
namespace App\Http\Controllers;

use App\Exports\GatewayExport;
use App\Models\DeviceGateway;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportGatewaysController extends Controller
{

    public function download(Request $request, $tab = null)
    {
        $search = $request->input('search') ?? '';
        $format = $request->input('format', 'csv');

        $header = [
            __('ID'),
            __('Gateway type'),
            __('Mac address'),
            __('Password'),
            __('Connected site'),
            __('Pstn'),
            __('Sim'),
            __('Sip'),
            __('Pbx'),
            __('Expires'),
            __('Enabled')
        ];

        $data = $this->getGatewayData($tab, $search);
        $rows = $this->formatData($data);

        $fileName = 'gateway_' . $tab . '--' . date('d-m-Y');

        return $format === 'xlsx'
            ? $this->downloadExcel($rows, $header, $fileName)
            : $this->downloadCsv($rows, $header, $fileName);
    }

    private function formatData($data)
    {
        return $data->map(function($row) {
            return [
                ($row->dg_id ?? ''),
                ($row->device->module->module_desc ?? $row->device->module->module_name ?? ''),
                ($row->dg_mac ?? ''),
                ($row->dg_sippwd ?? ''),
                ($row->device->device_site->ds_name ?? ''),
                ($row->device->device_site->pstn->number_value ?? ''),
                ($row->device->device_site->sim->number_value ?? ''),
                ($row->device->device_site->sip->number_value ?? ''),
                ($row->device->device_site->pbx->number_value ?? ''),
                ($row->dg_expires ?? ''),
                ($row->device->device_enabled ?? '0'),
            ];
        })->toArray();
    }

    private function downloadExcel($rows, $header, $fileName)
    {
        return Excel::download(
            new GatewayExport($rows, $header),
            $fileName . '.xlsx'
        );
    }

    private function downloadCsv($rows, $header, $fileName)
    {
        return response()->streamDownload(function() use ($rows, $header) {
            $file = fopen('php://output', 'w+');
            fputcsv($file, $header);

            foreach($rows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        }, $fileName . '.csv');
    }

    public function getGatewayData($tab, $search)
    {
        $baseQuery = DeviceGateway::query();
        if ($tab == 'enabled') {
            return $this->getGatewayQuery($baseQuery, $search)->forAccount()->enabled()->get();
        } elseif ($tab == 'disabled') {
            return $this->getGatewayQuery($baseQuery, $search)->forAccount()->disabled()->get();
        } elseif ($tab == 'assigned') {
            return $this->getGatewayQuery($baseQuery, $search)->forAccount()->assigned()->get();
        } elseif ($tab == 'unassigned') {
            return $this->getGatewayQuery($baseQuery, $search)->forAccount()->unassigned()->get();
        }
    }


    public function getGatewayQuery($rawQuery, $search)
    {
        return $rawQuery
            ->when($search, function($query, $search){
                $search = strtolower($search);
                $query = $query->where('dg_mac', 'like', '%'.$search.'%');
                $query = $query->orWhereHas('type', function ($query) use ($search) {
                    $query->where('dgt_type', 'like', '%'.$search.'%');
                });
                $query = $query->orWhereHas('device_site', function ($query) use ($search) {
                    $query->where('ds_name', 'like', '%'.$search.'%');
                });
                $query = $query->orWhereHas('numbers', function ($query) use ($search) {
                    $query->where('number_value', 'like', '%'.$search.'%');
                });
                return $query;
            })
            ->orderBy('dg_mac');
    }

}
