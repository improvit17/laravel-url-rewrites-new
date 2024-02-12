<?php

declare(strict_types=1);

namespace RuthgerIdema\UrlRewrite\Http;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use RuthgerIdema\UrlRewrite\Repositories\Interfaces\UrlRewriteInterface;

class UrlRewriteController
{
    /** @var UrlRewriteInterface */
    protected $repository;

    public function __construct(
        UrlRewriteInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function __invoke($url): object
    {
        if (! $urlRewrite = $this->repository->getByRequestPath($url)) {
            abort(404);
        }

        if ($urlRewrite->isForward()) {
            return $this->forwardResponse($urlRewrite->target_path);
        }

        return redirect($urlRewrite->target_path, $urlRewrite->getRedirectType());
    }

    protected function forwardResponse($url)
    {
        $url = tenant()->route('index') . '/'.ltrim($url, '/');

        return Route::dispatch(
            Request::create(
                $url,
                request()->getMethod()
            )
        );
    }
}
