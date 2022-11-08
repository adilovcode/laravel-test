<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model {

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * @param array $menuItems
     * @param string|null $menuItemId
     * @return array
     */
    public static function makeTree(array $menuItems, string $menuItemId = null): array {
        $treeCategories = [];

        foreach ($menuItems as $category) {
            if ($category['parent_id'] == $menuItemId) {

                $category['children'] = self::makeTree($menuItems, $category['id']);

                $treeCategories[] = $category;
            }
        }

        return $treeCategories;
    }
}
