<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ranking;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class RankingController extends Controller
{
    use Upload;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rankings = Ranking::get();
        return view('admin.ranking.index', compact('rankings'));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.ranking.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'rank_name' => 'required',
            'rank_lavel' => 'required',
            'rank_icon' => 'required',
        ];


        $this->validate($request, $rules);

        $rank = new Ranking();
        $rank->rank_name = $request->rank_name;
        $rank->rank_lavel = $request->rank_lavel;
        $rank->min_invest = isset($request->min_invest) ? $request->min_invest : 0;
        $rank->min_deposit = isset($request->min_deposit) ? $request->min_deposit : 0;
        $rank->min_earning = isset($request->min_earning) ? $request->min_earning : 0;
        $rank->description = $request->description;
        $rank->status = isset($request->status) ? 1 : 0;

        if ($request->hasFile('rank_icon')) {
            try {
                $image = $this->fileUpload($request->rank_icon, config('filelocation.rank.path'), null,null,'webp',80);
            } catch (\Exception $exp) {
                return back()->with('error', 'Image could not be uploaded.');
            }
        }

        if (isset($image)) {
            $rank->rank_icon = $image['path']??null;
            $rank->driver = $image['driver']??null;
        }
        $rank->save();
        return redirect()->route('admin.rankingsUser')->with('success', 'Ranking create successfully');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data['singleRanking'] = Ranking::findOrFail($id);
        return view('admin.ranking.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $rules = [
            'rank_name' => 'required',
            'rank_lavel' => 'required',
        ];

        $this->validate($request, $rules);

        $rank = Ranking::findOrFail($id);
        $rank->rank_name = $request->rank_name;
        $rank->rank_lavel = $request->rank_lavel;
        $rank->min_invest = isset($request->min_invest) ? $request->min_invest : 0;
        $rank->min_deposit = isset($request->min_deposit) ? $request->min_deposit : 0;
        $rank->min_earning = isset($request->min_earning) ? $request->min_earning : 0;
        $rank->description = $request->description;
        $rank->status = $request->status;

        if ($request->hasFile('rank_icon')) {
            try {
                $image = $this->fileUpload($request->rank_icon, config('filelocation.rank.path'), null,null,'webp',80);
            } catch (\Exception $exp) {
                return back()->with('error', 'Image could not be uploaded.');
            }
        }

        if (isset($image)) {
            $rank->rank_icon = $image['path']??null;
            $rank->driver = $image['driver']??null;
        }

        $rank->save();
        return redirect()->route('admin.rankingsUser')->with('success', 'Ranking Update successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Ranking::findOrFail($id)->delete();
        return back()->with('success', 'Delete Successfull');
    }

    public function sortBadges()
    {

    }

}
