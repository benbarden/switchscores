<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class ChartsDateController extends \App\Http\Controllers\BaseController
{
    public function showList()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Charts - Dates';

        $chartDates = \App\ChartsDate::orderBy('chart_date', 'desc')->get();

        $bindings['ChartDates'] = $chartDates;

        return view('admin.charts.date.list', $bindings);
    }

    public function add()
    {
        $request = request();
        if ($request->isMethod('post')) {
            \App\ChartsDate::create([
                'chart_date' => $request->chart_date,
                'stats_europe' => 'N',
                'stats_us' => 'N',
            ]);
            return redirect(route('admin.charts.date.list'));
        }
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Charts - Add date';

        return view('admin.charts.date.add', $bindings);
    }
}
