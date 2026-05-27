<?php

namespace App\Http\Controllers\Back;

use App\{
    Models\Item,
    Models\Gallery,
    Models\DiamondAttribute,
    Http\Requests\ItemRequest,
    Http\Controllers\Controller,
    Http\Requests\GalleryRequest,
    Repositories\Back\ItemRepository
};
use App\Helpers\ImageHelper;
use App\Models\Category;
use App\Models\ChieldCategory;
use App\Models\Currency;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ItemController extends Controller
{

    /**
     * Constructor Method.
     *
     * Setting Authentication
     *
     * @param  \App\Repositories\Back\ItemRepository $repository
     *
     */
    public function __construct(ItemRepository $repository)
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
        $this->repository = $repository;
    }


    public function summernoteUpload(Request $request)
    {
        $name = ImageHelper::uploadSummernoteImage($request->file('image'), 'images/summernote');

        return response()->json([
            'success' => true,
            'image' => url('/core/public/storage/images/summernote/' . $name)
        ]);
    }


    public function add()
    {
        return view('back.item.add');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $item_type = $request->has('item_type') ? ($request->item_type ? $request->item_type : '') : '';
        $is_type = $request->has('is_type') ? ($request->is_type ? $request->is_type : '') : '';
        $category_id = $request->has('category_id') ? ($request->category_id ? $request->category_id : '') : '';
        $orderby = $request->has('orderby') ? ($request->orderby ? $request->orderby : 'desc') : 'desc';

        $datas = Item::when($item_type, function ($query, $item_type) {
                return $query->where('item_type', $item_type);
            })
            ->when($is_type, function ($query, $is_type) {
                if ($is_type != 'outofstock') {
                    return $query->where('is_type', $is_type);
                } else {
                    return $query->whereStock(0)->whereItemType('normal');
                }
            })
            ->when($category_id, function ($query, $category_id) {
                return $query->where('category_id', $category_id);
            })
            ->when($orderby, function ($query, $orderby) {
                return $query->orderby('id', $orderby);
            })
            ->get();

        return view('back.item.index', [
            'datas' => $datas
        ]);
    }

    /**
     * Show the form for get subcategory a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getsubCategory(Request $request)
    {

        if ($request->category_id) {
            $data = Category::findOrFail($request->category_id);
            $data = $data->subcategory;
        } else {
            $data = [];
        }

        return response()->json(['data' => $data]);
    }

    /**
     * Show the form for get subcategory a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getChildCategory(Request $request)
    {

        if ($request->subcategory_id) {
            $data = Subcategory::findOrFail($request->subcategory_id);
            $data = $data->childcategory;
        } else {
            $data = [];
        }

        return response()->json(['data' => $data]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('back.item.create', [
            'curr' => Currency::where('is_default', 1)->first(),
            'completeTheLookSelected' => $this->completeTheLookSelectedFromRequestOrItem(),
        ]);
    }

    /**
     * AJAX search for Complete The Look multi-select (Select2).
     */
    public function searchCompleteTheLook(Request $request)
    {
        $term = trim((string) $request->get('q', ''));
        $excludeId = (int) $request->get('exclude_id', 0);

        $query = Item::query()
            ->where('item_type', 'normal')
            ->where('status', 1)
            ->when($excludeId > 0, fn ($q) => $q->where('id', '!=', $excludeId));

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('sku', 'like', '%' . $term . '%');
            });
        }

        $items = $query->orderBy('name')->limit(40)->get(['id', 'name', 'sku']);

        return response()->json([
            'results' => $items->map(fn (Item $item) => [
                'id' => $item->id,
                'text' => $item->name . ($item->sku ? ' (' . $item->sku . ')' : ''),
            ])->values(),
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Item>
     */
    protected function completeTheLookSelectedFromRequestOrItem(?Item $item = null)
    {
        $old = old('complete_the_look_ids');
        if ($old !== null) {
            $ids = is_array($old) ? array_map('intval', $old) : Item::parseCompleteTheLookIds($old);
        } elseif ($item && filled($item->complete_the_look_ids)) {
            $ids = Item::parseCompleteTheLookIds($item->complete_the_look_ids);
        } else {
            return collect();
        }

        $ids = array_values(array_filter(array_unique($ids), fn (int $id) => $id > 0));
        if ($ids === []) {
            return collect();
        }

        $order = array_flip($ids);

        return Item::query()
            ->whereIn('id', $ids)
            ->where('item_type', 'normal')
            ->get(['id', 'name', 'sku'])
            ->sortBy(fn (Item $row) => $order[$row->id] ?? 9999)
            ->values();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ItemRequest $request)
    {
        $item_id = $this->repository->store($request);

        // Diamond details (one-to-one) — independent of item type.
        $item = Item::find($item_id);
        if ($item) {

            $hasDiamond = (bool) $request->input('has_diamond');

            if ($hasDiamond) {

                $certificatePdfPath = null;
                $certificateImagePath = null;

                /*
                |--------------------------------------------------------------------------
                | Certificate PDF Upload
                |--------------------------------------------------------------------------
                */

                if ($request->hasFile('certificate_report_pdf')) {

                    $pdf = $request->file('certificate_report_pdf');

                    $pdfName = time() . '_pdf_' . $pdf->getClientOriginalName();

                    $pdf->storeAs(
                        'public/certificates',
                        $pdfName
                    );

                    $certificatePdfPath = 'certificates/' . $pdfName;
                }

                /*
                |--------------------------------------------------------------------------
                | Certificate Image Upload
                |--------------------------------------------------------------------------
                */

                if ($request->hasFile('certificate_report_image')) {

                    $image = $request->file('certificate_report_image');

                    $imageName = time() . '_image_' . $image->getClientOriginalName();

                    $image->storeAs(
                        'public/certificates',
                        $imageName
                    );

                    $certificateImagePath = 'certificates/' . $imageName;
                }

                DiamondAttribute::updateOrCreate(

                    ['item_id' => $item->id],

                    [

                        'carat_weight' => $request->input('carat_weight'),

                        'shape' => Item::normalizeJewelryOptionList(
                            $request->input('shape')
                        ),

                        'cut_grade' => $request->input('cut_grade'),

                        'color_grade' => $request->input('color_grade'),

                        'clarity_grade' => $request->input('clarity_grade'),

                        'lab' => $request->input('lab'),

                        'certificate_number' => $request->input('certificate_number'),

                        'video_360_url' => $request->input('video_360_url'),

                        'is_lab_grown' => (bool) $request->input('is_lab_grown'),

                        'certificate_report_pdf' => $certificatePdfPath,

                        'certificate_report_image' => $certificateImagePath,

                    ]
                );

            } else {

                DiamondAttribute::where('item_id', $item->id)->delete();
            }

        }

        if ($request->is_button == 0) {
            return redirect()->route('back.item.index')->withSuccess(__('Product Added Successfully.'));
        } else {
            return redirect(route('back.item.edit', $item_id))->withSuccess(__('Product Added Successfully.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Item $item)
    {
        return view('back.item.edit', [
            'item' => $item,
            'curr' => Currency::where('is_default', 1)->first(),
            'social_icons' => json_decode($item->social_icons, true),
            'social_links' => json_decode($item->social_links, true),
            'specification_name' => json_decode($item->specification_name, true),
            'specification_description' => json_decode($item->specification_description, true),
            'completeTheLookSelected' => $this->completeTheLookSelectedFromRequestOrItem($item),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\ItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(ItemRequest $request, Item $item)
    {
        $this->repository->update($item, $request);

        // Diamond details (one-to-one) — independent of item type.
        $hasDiamond = (bool) $request->input('has_diamond');
        if ($hasDiamond) {

            $diamond = DiamondAttribute::firstOrNew([
                'item_id' => $item->id
            ]);

            /*
            |--------------------------------------------------------------------------
            | Existing paths preserve
            |--------------------------------------------------------------------------
            */

            $certificatePdfPath = $diamond->certificate_report_pdf;

            $certificateImagePath = $diamond->certificate_report_image;

            /*
            |--------------------------------------------------------------------------
            | Replace PDF
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('certificate_report_pdf')) {

                if ($certificatePdfPath &&
                    Storage::exists('public/' . $certificatePdfPath)) {

                    Storage::delete('public/' . $certificatePdfPath);
                }

                $pdf = $request->file('certificate_report_pdf');

                $pdfName = time() . '_pdf_' . $pdf->getClientOriginalName();

                $pdf->storeAs(
                    'public/certificates',
                    $pdfName
                );

                $certificatePdfPath = 'certificates/' . $pdfName;
            }

            /*
            |--------------------------------------------------------------------------
            | Replace Image
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('certificate_report_image')) {

                if ($certificateImagePath &&
                    Storage::exists('public/' . $certificateImagePath)) {

                    Storage::delete('public/' . $certificateImagePath);
                }

                $image = $request->file('certificate_report_image');

                $imageName = time() . '_image_' . $image->getClientOriginalName();

                $image->storeAs(
                    'public/certificates',
                    $imageName
                );

                $certificateImagePath = 'certificates/' . $imageName;
            }

            DiamondAttribute::updateOrCreate(

                ['item_id' => $item->id],

                [

                    'carat_weight' => $request->input('carat_weight'),

                    'shape' => Item::normalizeJewelryOptionList(
                        $request->input('shape')
                    ),

                    'cut_grade' => $request->input('cut_grade'),

                    'color_grade' => $request->input('color_grade'),

                    'clarity_grade' => $request->input('clarity_grade'),

                    'lab' => $request->input('lab'),

                    'certificate_number' => $request->input('certificate_number'),

                    'video_360_url' => $request->input('video_360_url'),

                    'is_lab_grown' => (bool) $request->input('is_lab_grown'),

                    'certificate_report_pdf' => $certificatePdfPath,

                    'certificate_report_image' => $certificateImagePath,

                ]
            );

        } else {

            DiamondAttribute::where('item_id', $item->id)->delete();
        }

        if ($request->is_button == 0) {
            return redirect()->route('back.item.index')->withSuccess(__('Product Updated Successfully.'));
        } else {
            return redirect()->back()->withSuccess(__('Product Updated Successfully.'));
        }
    }

    /**
     * Change the status for editing the specified resource.
     *
     * @param  int  $id
     * @param  int  $status
     * @return \Illuminate\Http\Response
     */
    public function status(Item $item, $status)
    {
        $item->update(['status' => $status]);
        return redirect()->back()->withSuccess(__('Status Updated Successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Item $item)
    {
        $this->repository->delete($item);
        return redirect()->back()->withSuccess(__('Product Deleted Successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function galleries(Item $item)
    {
        return view('back.item.galleries', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\GalleryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function galleriesUpdate(GalleryRequest $request)
    {
        $this->repository->galleriesUpdate($request);
        return redirect()->back()->withSuccess(__('Gallery Information Updated Successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function galleryDelete(Gallery $gallery)
    {
        $this->repository->galleryDelete($gallery);
        return redirect()->back()->withSuccess(__('Successfully Deleted From Gallery.'));
    }


    public function highlight(Item $item)
    {
        return view('back.item.highlight', [
            'item' => $item
        ]);
    }
    public function highlight_update(Item $item, Request $request)
    {
        $this->repository->highlight($item, $request);
        return redirect()->route('back.item.index')->withSuccess(__('Product Updated Successfully.'));
    }




    // ---------------- DIGITAL PRODUCT START ---------------//

    public function deigitalItemCreate()
    {
        return view('back.item.digital.create', [
            'curr' => Currency::where('is_default', 1)->first()
        ]);
    }

    public function deigitalItemStore(ItemRequest $request)
    {
        $this->repository->store($request);
        return redirect()->route('back.item.index')->withSuccess(__('New Product Added Successfully.'));
    }

    public function deigitalItemEdit($id)
    {
        $item = Item::findOrFail($id);

        return view('back.item.digital.edit', [
            'item' => $item,
            'curr' => Currency::where('is_default', 1)->first(),
            'social_icons' => json_decode($item->social_icons, true),
            'social_links' => json_decode($item->social_links, true),
            'specification_name' => json_decode($item->specification_name, true),
            'specification_description' => json_decode($item->specification_description, true),
        ]);
    }


    // ---------------- LICENSE PRODUCT START ---------------//

    public function licenseItemCreate()
    {
        return view('back.item.license.create', [
            'curr' => Currency::where('is_default', 1)->first()
        ]);
    }

    public function licenseItemStore(ItemRequest $request)
    {
        $this->repository->store($request);
        return redirect()->route('back.item.index')->withSuccess(__('New Product Added Successfully.'));
    }

    public function licenseItemEdit($id)
    {
        $item = Item::findOrFail($id);

        return view('back.item.license.edit', [
            'item' => $item,
            'curr' => Currency::where('is_default', 1)->first(),
            'social_icons' => json_decode($item->social_icons, true),
            'social_links' => json_decode($item->social_links, true),
            'specification_name' => json_decode($item->specification_name, true),
            'specification_description' => json_decode($item->specification_description, true),
            'license_name' => json_decode($item->license_name, true),
            'license_key' => json_decode($item->license_key, true),
        ]);
    }


    public function stockOut()
    {
        $datas = Item::where('item_type', 'normal')->where('stock', 0)->get();
        return view('back.item.stockout', compact('datas'));
    }
}
