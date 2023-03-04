<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

if (array_key_exists('brands', $suggestions)) {
    $brands = [];
    foreach ($suggestions['brands'] as $brandId => $name) {
        $brands[] = [
            'brand_id' => $brandId,
            'brand_name' => $name,
        ];
    }
    $suggestions['brands'] = $brands;
}

if (array_key_exists('categories', $suggestions)) {
    $categories = [];
    foreach ($suggestions['categories'] as $categoryId => $name) {
        $categories[] = [
            'category_id' => $categoryId,
            'category_name' => $name,
        ];
    }
    $suggestions['categories'] = $categories;
}

$data['suggestions'] = $suggestions;

if (1 > count($suggestions)) {
    $status = applicationConstants::OFF;
}
