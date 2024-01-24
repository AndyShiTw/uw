<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Services\Post\CreatePostService;
use App\Services\Post\GetPostListService;
use App\Services\Post\UpdatePostService;
use App\Services\Post\DeletePostService;
use App\Services\Post\GetPostCustomListService;
use App\Services\Post\GetUserPostListService;
use App\Services\Post\InactivePostService;
use App\Services\Post\activePostService;
use Illuminate\Http\Request;

class PostController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function createPost(Request $request)
    {
        $service = app()->make(CreatePostService::class);
        $service->handle($request->json()->all());
        if (!$service->getProcessResult()) {
            return $this->apiResponse('', 500, (object) []);
        }
        return $this->apiResponse('', 0, $service->getData());
    }

    public function getPostList(Request $request)
    {
        $service = app()->make(GetPostListService::class);
        $service->handle($request->all());
        if (!$service->getProcessResult()) {
            return $this->apiResponse('', 500, (object) []);
        }
        return $this->apiResponse('', 0, $service->getData());
    }

    public function updatePost(Request $request)
    {
        $service = app()->make(UpdatePostService::class);
        $service->handle($request->json()->all());
        if (!$service->getProcessResult()) {
            return $this->apiResponse('', 500, (object) []);
        }
        return $this->apiResponse('', 0, $service->getData());
    }

    public function deletePost(Request $request)
    {
        $service = app()->make(DeletePostService::class);
        $service->handle($request->json()->all());
        if (!$service->getProcessResult()) {
            return $this->apiResponse('', 500, (object) []);
        }
        return $this->apiResponse('', 0, $service->getData());
    }

    public function getPostCustomList(Request $request)
    {
        $service = app()->make(GetPostCustomListService::class);
        $service->handle($request->all());
        if (!$service->getProcessResult()) {
            return $this->apiResponse('', 500, (object) []);
        }
        return $this->apiResponse('', 0, $service->getData());
    }

    public function getUserPostList(Request $request)
    {
        $service = app()->make(GetUserPostListService::class);
        $service->handle($request->all());
        if (!$service->getProcessResult()) {
            return $this->apiResponse('', 500, (object) []);
        }
        return $this->apiResponse('', 0, $service->getData());
    }

    public function inactivePost(Request $request)
    {
        $service = app()->make(InactivePostService::class);
        $service->handle($request->all());
        if (!$service->getProcessResult()) {
            return $this->apiResponse('', 500, (object) []);
        }
        return $this->apiResponse('', 0, $service->getData());
    }

    public function activePost(Request $request)
    {
        $service = app()->make(activePostService::class);
        $service->handle($request->all());
        if (!$service->getProcessResult()) {
            return $this->apiResponse('', 500, (object) []);
        }
        return $this->apiResponse('', 0, $service->getData());
    }
}
