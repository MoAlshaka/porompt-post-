<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    //
    public function index(Request $request){
    $user=User::where('access_token',$request->access_token)->first();
        $posts= Post::with(['user:id,name','reacts'=> function($query) use ($user){
           $query->select('post_user.post_id','post_user.user_id'); 
           $query->where('user_id',$user->id); 
        }])->get();
        // $posts= $posts->map(function ($post){
        //     return [
        //         'id'=>$post->id,
        //         'desc'=>$post->desc,
        //         'negative'=>$post->negative,
        //         'image'=>"public/images/posts/$post->image",
        //         'created_at'=>$post->created_at,
        //         'updated_at'=>$post->updated_at,
        //         'user'=>[
        //             'id'=>$post->user->id,
        //             'name'=>$post->user->name,
        //         ],
        //         'reacts'=>$post->reacts ?$post->reacts->map(function($react){
        //             return [
        //                 'post_id'=>$react->post_id,
        //                 'user_id'=>$react->user_id,
        //             ];
        //         }): null,
        //     ];
        // });
        return response()->json(PostResource::collection($posts));
    } 

    public function show(Request $request,$id){
        $user=User::where('access_token',$request->access_token)->first();
        $post= Post::with(['user:id,name','reacts'=> function($query) use ($user){
            $query->select('post_user.post_id','post_user.user_id'); 
            $query->where('user_id',$user->id); 
         }])->find($id);
        if ( $post == null ) {
            return response()->json('There is no post found');
        } 
            return response()->json(new PostResource($post));
    }

    public function store(Request $request){
        $valdiator=Validator::make($request->all(),[
            'desc'=>'required',
            'negative'=>'required',
            'image'=>'required|image|mimes:png,jpg,jpeg',
        ]);

        if ($valdiator->fails()) {
            $errors=$valdiator->errors();
            return response($errors);
        }
        $image=$request->image;
        $ext=$image->getClientOriginalExtension();
        $new_name=uniqid() . '.' . $ext;
        $image->move(public_path('images/posts'),$new_name);
        $user=User::where('access_token',$request->access_token)->first();
        $post=Post::create([
            'negative'=>$request->negative,
            'desc'=>$request->desc,
            'image'=>$new_name,
            'user_id'=>$user->id,
        ]);
        $data=[
            'id'=>$post->id,
            'description'=>$post->desc,
            'negative'=>$post->negative,
            'image'=>"public/images/posts/$post->image",
            'author'=>$user->name,
            'reactions'=>$post->reactions,
            'created_at'=>$post->created_at,
            'updated_at'=>$post->updated_at,
        ];
        return response()->json($data);
    }

    public function update(Request $request,$id){
        $valdiator=Validator::make($request->all(),[
            'desc'=>'required',
            'negative'=>'required',
            'image'=>'nullable|image|mimes:png,jpg,jpeg',
        ]);

        if ($valdiator->fails()) {
            $errors=$valdiator->errors();
            return response($errors);
        }
        $user=User::where('access_token',$request->access_token)->first();
        $post=Post::with('user')->find($id);
        $image_name=$post->image;
        if($user->id == $post->user_id){
            if($request->hasFile('image')){
                unlink(public_path("images/posts/$image_name"));
                $image=$request->image;
                $ext=$image->getClientOriginalExtension();
                $image_name=uniqid() . '.' . $ext;
                $image->move(public_path('images/posts'),$image_name);
            }
            
            $post->update([
                'negative'=>$request->negative,
                'desc'=>$request->desc,
                'image'=>$image_name,
            ]);
            $data=[
                'id'=>$post->id,
                'description'=>$post->desc,
                'negative'=>$post->negative,
                'image'=>"public/images/posts/$post->image",
                'author'=>$user->name,
                'reactions'=>$post->reactions,
                'created_at'=>$post->created_at,
                'updated_at'=>$post->updated_at,
            ];
            return response()->json($data);
        }else {
            return response()->json('you have not access to update');
        }
    }

    public function delete(Request $request,$id){
        $user=User::where('access_token',$request->access_token)->first();
        $post=Post::find($id);
        $image=$post->image;
        if($user->id == $post->user_id){
           unlink(public_path("images/posts/$image"));
           $post->delete();
            return response()->json('deleted successful');
        }else {
            return response()->json('you have not access to delete');
        }
    }

    public function like(Request $request,$id){
        $post=Post::find($id);
        $user=User::where('access_token',$request->access_token)->first();
        if(!$post){
            return response()->json('There is no post found');
        }
        $reacts=$post->reacts()->wherepivot('user_id',$user->id)->exists();
        if($reacts){
            return response()->json('you react before ');
        }
        $post->update([
            'reactions'=>$post->reactions+=1
        ]);
        $post->reacts()->attach($user->id);
        return response()->json('you react love');
      
        
    }

    public function dislike(Request $request,$id){
        $post=Post::find($id);
        $user=User::where('access_token',$request->access_token)->first();
        if(!$post){
            return response()->json('There is no post found');
        }
        $reacts=$post->reacts()->wherepivot('user_id',$user->id)->exists();
        if(!$reacts){
            return response()->json('you did not react before ');
        }
        $post->update([
            'reactions'=>$post->reactions-=1
        ]);
        $post->reacts()->detach($user->id);
        
        return response()->json('you remove react love');
        
    }


}
