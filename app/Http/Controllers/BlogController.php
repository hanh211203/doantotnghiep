<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //controller can goi den Model (Model lay du lieu tu DB)
        // tao doi tuong cua Model
        $obj = new Blog();
        // lay du lieu ben Model bang cach goi function ben Model do (vẫn hứng vào 1 biên $blogs)
        $blogs = $obj->index();
        // hien thi view va truyen DL sang, co the truyen nhieu bien cung luc duoc
        return view('blogs.index', [
            'blogs' => $blogs
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //ten thu muc.tenview de hien thi giao dien
        return view('blogs.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBlogRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBlogRequest $request)
    {
        if($request->validated()) {
            if ($request->hasFile('file_upload')) {
                $file = $request->file('file_upload');
                $ext = $file->extension();
                $file_name = time() . '-' . 'blog.' . $ext;
                // Lưu file vào thư mục storage/app/public
                // $file->storeAs('public/Admin/', $file_name);
                $file->move(public_path('storage/Admin/'), $file_name);

                // Gộp đường dẫn ảnh vào request để lưu vào DB
                $request->merge(['image' => $file_name]);
            } else {
                // Nếu không có file upload, gán null cho image
                $request->merge(['image' => null]);
            }

            $obj = new Blog();
            // Lấy dữ liệu từ form và gán vào thuộc tính của đối tượng
            $obj->title_blog = $request->title_blog;
            $obj->description_blog = $request->description_blog;
            $obj->content_blog = $request->content_blog;
            $obj->posting_date_time = $request->posting_date_time;
            $obj->image = $request->image; // Lưu đường dẫn ảnh vào DB

            // Gọi function bên model để lưu đối tượng
            $obj->store(); // Lưu dữ liệu vào DB

            // Quay lại trang danh sách
            return Redirect::route('blogs.index');
        } else {
            return Redirect::back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function show(Blog $blog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function edit(Blog $blog, Request $request)
    {
        $obj = new Blog();
        // lay id cua Blog dang can sua va gan vao thuoc tinh cua doi tuong
        $obj->id = $request->id;
        // goi function lay DL theo ID tu Model
        $blogs = $obj->edit();
        return view('blogs.edit', [
            'blogs' => $blogs,
            'id' => $obj->id
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBlogRequest  $request
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBlogRequest $request, Blog $blog)
    {
        // Kiểm tra nếu có file upload mới
        if ($request->hasFile('file_upload')) {
            // Xóa ảnh cũ trước khi upload ảnh mới (nếu có)
            if ($blog->image) {
                Storage::delete('storage/Admin/' . $blog->image);
            }

            // Upload file ảnh mới
            $file = $request->file('file_upload');
            $ext = $file->getClientOriginalExtension();
            $file_name = time() . '-blog.' . $ext;
            $file->move(public_path('storage/Admin/'), $file_name);

            // Cập nhật tên ảnh mới vào request để lưu vào DB
            $request->merge(['image' => $file_name]);
        } else {
            // Nếu không có file upload mới, giữ nguyên ảnh cũ
            $request->merge(['image' => null]);
        }

        // Cập nhật các trường dữ liệu khác của blog
        $obj = new Blog();
        $obj->id = $request->id;
        $obj->title_blog = $request->title_blog;
        $obj->description_blog = $request->description_blog;
        $obj->content_blog = $request->content_blog;
        $obj->posting_date_time = $request->posting_date_time;
        $obj->image = $request->image;
        // goi function update ben model
        $obj->updateBlog();
        return Redirect::route('blogs.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function destroy(Blog $blog, Request $request)
    {
        $obj = new Blog();
        $obj->id = $request->id;
        $obj->deleteBlog();
        return Redirect::route('blogs.index');
    }

    public function showBlog($id)
    {
        $blog = Blog::find($id); // Lấy blog dựa trên ID
        return view('blogs.show', [
            'blog' => $blog,
            'id' => $id
        ]);
    }
}
