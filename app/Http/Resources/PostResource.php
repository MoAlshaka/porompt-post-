<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return[
            'id'=>$this->id,
            'description'=>$this->desc,
            'negative'=>$this->negative,
            'image'=>"public/images/posts/$this->image",
            'reactions'=>$this->reactions,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
            'author'=>[
                'id'=>$this->user->id,
                'name'=>$this->user->name,
            ],
            'reacts'=>$this->reacts ?$this->reacts->map(function($react){
                return [
                    'post_id'=>$react->post_id,
                    'user_id'=>$react->user_id,
                    'react'=>'like',
                ];
            }): null,
        ];
        
    }
}
