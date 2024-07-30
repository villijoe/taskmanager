<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;    

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Category $category)
    {
        echo $user->id === $category->user_id || $user->role === 'ADMIN';
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    { echo $user->role === 'USER' || $user->role === 'ADMIN';
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Category $category)
    {
        echo $user->id;
        echo $category->user_id;
        echo $user->id === $category->user_id || $user->role === 'ADMIN';
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Category $category)
    {
        return $user->id === $category->user_id || $user->role === 'ADMIN';
    }
}
