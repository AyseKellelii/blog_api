<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    // Kullanıcı bu kategoriyi görüntüleyebilir mi?
    public function view(User $user, Category $category): bool
    {
        return $user->id === $category->user_id;
    }

    // Güncelleyebilir mi?
    public function update(User $user, Category $category): bool
    {
        return $user->id === $category->user_id;
    }

    // Silebilir mi?
    public function delete(User $user, Category $category): bool
    {
        return $user->id === $category->user_id;
    }
}
