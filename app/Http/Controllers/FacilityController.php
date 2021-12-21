<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Facility;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FacilityController extends Controller
{
    public function store(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    Rule::unique(Facility::class)
                ],
                'start_stock' => [
                    'required',
                    'integer',
                ],
            ]
        );

        if ($validate->fails()) {
            $data = [
                'validation_errors' => $validate->errors(),
            ];

            return ResponseFormatter::error(401, 'Validation Errors', $data);
        }

        try {
            $check_facility = Facility::select('*')
                ->withTrashed()
                ->whereDate('created_at', '>=', date('Y-m-d') . ' 00:00:00')
                ->count();
            
            if ($check_facility === 0) {
                $id = 'F' . date('dmy') . '0001';
            } else {
                $item = $check_facility + 1;
                if ($item < 10) {
                    $id = 'F' . date('dmy') . '000' . $item;
                } elseif ($item >= 10 && $item <= 99) {
                    $id = 'F' . date('dmy') . '00' . $item;
                } elseif ($item >= 100 && $item <= 999) {
                    $id = 'F' . date('dmy') . '0' . $item;
                } elseif ($item >= 1000 && $item <= 9999) {
                    $id = 'F' . date('dmy') . $item;
                }
            }

            $facility = Facility::create([
                'id' => $id,
                'name' => $request->name,
                'start_stock' => $request->start_stock,
                'remaining_stock' => $request->start_stock,
            ]);

            $data = [
                'facility' => $facility
            ];

            return ResponseFormatter::success('Success Store Category', $data);
        } catch (QueryException $error) {
            $data = [
                'error' => $error
            ];

            return ResponseFormatter::error(500, 'Query Error', $data);
        }
    }

    public function showAll()
    {
        $facility = Facility::get();

        $data = [
            'facility' => $facility
        ];

        return ResponseFormatter::success('All Facility', $data);
    }

    public function show($id)
    {
        $facility = Facility::where('id', $id)->first();
        
        $data = [
            'facility' => $facility
        ];

        return ResponseFormatter::success('Facility ' . $facility->name, $data);
    }

    public function update(Request $request, $id)
    {
        $facility = Facility::where('id', $id)->first();
    
        $request->name === null ? $name = $facility->name : $name = $request->name;

        if ($request->start_stock === null) {
            $start_stock = $facility->start_stock;
            $remaining_stock = $facility->remaining_stock;
        } else {
            if ($request->start_stock < $facility->start_stock) {
                return ResponseFormatter::error(401, 'Error Stock');
            } else {
                $start_stock = $request->start_stock;
                $remaining_stock = $request->start_stock + $facility->remaining_stock;
            }
        }

        $update = Facility::where('id', $id)
            ->update([
                'name' => $name,
                'start_stock' => $start_stock,
                'remaining_stock' => $remaining_stock,
            ]);

        $data = [
            'facility' => $update
        ];

        return ResponseFormatter::success('Success Update Facility ' . $facility->name, $data);
    }

    public function destroy($id)
    {
        Facility::where('id', $id)->delete();
    
        return ResponseFormatter::success('Success Delete Facility ' . $id);
    }
}
