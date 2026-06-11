<?php

use App\Models\Gallery;
use App\Services\FeatureGateService;
use Illuminate\Support\Facades\Route;

Route::get('/test-gallery-api', function (FeatureGateService $featureGateService) {
    $galleries = Gallery::all(['id', 'slug', 'name', 'tenant_id']);
    
    $results = [];
    foreach ($galleries as $gallery) {
        $featureEnabled = $featureGateService->isEnabled('gallery', $gallery->tenant_id);
        $results[] = [
            'slug' => $gallery->slug,
            'name' => $gallery->name,
            'tenant_id' => $gallery->tenant_id,
            'feature_enabled' => $featureEnabled,
            'api_url' => url("/api/v1/galleries/{$gallery->slug}"),
        ];
    }
    
    return response()->json([
        'galleries' => $results,
        'message' => 'If feature_enabled is false, that\'s why the API returns 404'
    ]);
});
