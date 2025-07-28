<?php

// app/Http/Controllers/ImportExportController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PenerimasExport;
use App\Imports\PenerimasImport;

class ImportExportController extends Controller
{
    public function export()
    {
        return Excel::download(new PenerimasExport, 'penerima_bantuan.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new PenerimasImport, $request->file('file'));

    return back()->with('import_success', 'Data berhasil diimpor!');
    }
}
