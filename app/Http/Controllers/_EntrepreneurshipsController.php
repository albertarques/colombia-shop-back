<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entrepreneurship;
use App\Models\Comment;
use App\Models\User;
use App\Models\Category;
use App\Models\InspectionState;
use Illuminate\Support\Facades\Auth;

class EntrepreneurshipsController extends Controller
{
    //
    public function __construct(){
        $this->middleware('api');
    }

    public function index(){
      $entrepreneurships = Entrepreneurship::all();

      return response()->json([
        'status'=>'success',
        'entrepreneurships'=>$entrepreneurships,
      ]);
    }

    public function approvedIndex(){
        // TODO: Obtiene todos los emprendimientos aprovados y todas las categorías.
        $entrepreneurships = Entrepreneurship::all();
        $category = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'entrepreneurships' => [...$entrepreneurships],
            // 'categories' => $category,
        ]);
    }

    public function pendingIndex(){
        // TODO: Obtiene todos los emprendimientos pendientes de aprovación.
        $entrepreneurships = Entrepreneurship::all()->where('inspection_state', '=', 1);

        return response()->json([
            'status' => 'success',
            'entrepreneurships' => [...$entrepreneurships],
        ]);
    }

    public function availableIndex(){
        // TODO: Obtiene todos los emprendimientos aprovados y disponibles, y todas las categorías.
        $entrepreneurships = Entrepreneurship::all()->where('inspection_state', '=', 2)->where('availability_state', '=', 2);
        // $category = Category::all();

        return response()->json([
            'status' => 'success',
            'entrepreneurships' => [...$entrepreneurships],
        ]);
    }

    public function myEntrepreneurships(){
      // Obtener el usuario autenticado
      $user = auth()->user();

      // Obtener los emprendimientos asociados al usuario
      $entrepreneurships = $user->entrepreneurships;

      // Retornar los emprendimientos en formato JSON
      return response()->json($entrepreneurships);
    }

    public function store(Request $request){
        $request->validate([
            'title' => 'required|string|max:100',
            'product_img' => 'image|max:2048',
            'description' => 'required|string|max:100',
            'price' => 'required|integer',


            // 'user_id' => 'required|integer|exists:users,id',
            // 'title' => 'required|string|max:255',
            // 'logo' => 'nullable|url',
            // 'product_img' => 'nullable|url',
            // 'description' => 'required|string|max:500',
            // 'price' => 'required'|'numeric|regex:/^\d+(\.\d{1,2})?$/',
            // 'category_id' => 'required|exists:categories,id',
            // 'avg_score' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
            // 'cash_payment' => 'required|boolean',
            // 'card_payment' => 'required|boolean',
            // 'bizum_payment' => 'required|boolean',
            // 'stock' => 'required|integer|max:500',
            // 'availability_state' => 'required|integer|exists:availability_states,id|between:1, 2',
            // 'phone' => 'required|string|digits_between:9,15',
            // 'email' => 'required|email',
            // 'location' => 'required|string|max:255',
            // 'inspection_state' => 'required|integer|exists:inspection_states,id|between:1, 3',
        ]);

        $imagePath = $request->file('image')->store('public/images');
        $imageUrl = url('storage/' . str_replace('public/', '', $imagePath));

        $entrepreneurship = Entrepreneurship::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'logo' => $request->logo,
            'product_img' => $request->product_img,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'avg_score' => $request->avg_score,
            'cash_payment' => $request->cash_payment,
            'card_payment' => $request->card_payment,
            'bizum_payment' => $request->bizum_payment,
            'stock' => $request->stock,
            'availability_state' => 2,
            'phone' => $request->phone,
            'email' => $request->email,
            'location' => $request->location,
            'inspection_state' => 2,
        ]);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Entrepreneurship created successfully',
            'entrepreneurship' => $entrepreneurship,
        ]);
    }

    public function show($id){
        // Obtiene el emprendimiento con su categoria, sus comentarios y el usuario propietário.
        $entrepreneurship = Entrepreneurship::find($id);
        $comments = Comment::all()->where('entrepreneurship_id', '=', $id);
        $user_id = $entrepreneurship->user_id;
        $user = User::all()->where('id', '=', $user_id);
        $category_id = $entrepreneurship->category_id;
        $category = Category::all()->where('id', '=', $category_id);


        return response()->json([
            'status' => 'success',
            'category' => $category,
            'entrepreneurship' => $entrepreneurship,
            'comments' => $comments,
            'user' => $user,
        ]);
    }

    public function updateMyEntrepreneurship(Request $request, $entrepreneurship_id){

      $entrepreneurship = Entrepreneurship::find($entrepreneurship_id);
      $user = User::find($entrepreneurship->user_id);

      $validatedData = $request->validate([
        'title' => 'required|max:255',
        'logo' => 'nullable|image|max:1024',
        'product_img' => 'nullable|image|max:1024',
        'description' => 'required',
        'price' => 'required|numeric',
        'category_id' => 'required|exists:categories,id',
        'avg_score' => 'nullable|numeric|min:0|max:5',
        'cash_payment' => 'required|boolean',
        'card_payment' => 'required|boolean',
        'bizum_payment' => 'required|boolean',
        'stock' => 'required|integer|min:0',
        'availability_state' => 'required|in:disponible,no_disponible',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'location' => 'nullable|max:255',
        'id' => '$entrepreneurship->id',
        'user_id' => 'prohibited',
        'inspection_state' => 'prohibited',
        'created_at' => 'prohibited',
        'updated_at' => 'prohibited'
      ]);


      if (!$entrepreneurship) {
          return response()->json(['message' => 'El emprendimiento no existe'], 404);
      }

      if ($entrepreneurship->user_id != $user->id) {
          return response()->json(['message' => 'No tienes permiso para actualizar este emprendimiento'], 403);
      }

      // if ($request->hasFile('logo')) {
      //     $logo = $request->file('logo');
      //     $logoName = time() . '_' . $logo->getClientOriginalName();
      //     $logo->move(public_path('images'), $logoName);
      //     $validatedData['logo'] = $logoName;
      // }

      // if ($request->hasFile('product_img')) {
      //     $productImg = $request->file('product_img');
      //     $productImgName = time() . '_' . $productImg->getClientOriginalName();
      //     $productImg->move(public_path('images'), $productImgName);
      //     $validatedData['product_img'] = $productImgName;
      // }

      $entrepreneurship->update($validatedData);

      return response()->json(['message' => 'Emprendimiento actualizado correctamente'], 200);
    }

    public function update(Request $request, $id){
        $request->validate([
          'user_id' => 'required|integer|exists:users,id',
          'title' => 'required|string|max:255',
          'logo' => 'nullable|url',
          'product_img' => 'nullable|url',
          'description' => 'required|string|max:500',
          'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
          'category_id' => 'required|exists:categories,id',
          'avg_score' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
          'cash_payment' => 'required|boolean',
          'card_payment' => 'required|boolean',
          'bizum_payment' => 'required|boolean',
          'stock' => 'required|integer|max:500',
          'availability_state' => 'required|integer|exists:availability_states,id|between:1, 2',
          'phone' => 'required|string|digits_between:9,15',
          'email' => 'required|email',
          'location' => 'required|string|max:255',
          'inspection_state' => 'required|integer|exists:inspection_states,id|between:1, 3',
        ]);

        $entrepreneurship = Entrepreneurship::find($id);
        $entrepreneurship->id = $request->id;
        $entrepreneurship->user_id = $request->user_id;
        $entrepreneurship->title = $request->title;
        $entrepreneurship->logo = $request->logo;
        $entrepreneurship->product_img = $request->product_img;
        $entrepreneurship->description = $request->description;
        $entrepreneurship->price = $request->price;
        $entrepreneurship->category_id = $request->category_id;
        $entrepreneurship->avg_score = $request->avg_score;
        $entrepreneurship->cash_payment = $request->cash_payment;
        $entrepreneurship->card_payment = $request->card_payment;
        $entrepreneurship->bizum_payment = $request->bizum_payment;
        $entrepreneurship->stock = $request->stock;
        $entrepreneurship->availability_state = $request->availability_state;
        $entrepreneurship->phone = $request->phone;
        $entrepreneurship->email = $request->email;
        $entrepreneurship->location = $request->location;
        $entrepreneurship->inspection_state = $request->inspection_state;
        $entrepreneurship->save();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Entrepreneurship updated successfully',
            'entrepreneurship' => $entrepreneurship,
        ]);
    }

    public function updateInspectionState(Request $request, $entrepreneurship_id){

      $entrepreneurship = Entrepreneurship::find($entrepreneurship_id);
      $user = Auth::user();

      $request->validate([
        'inspection_state' => 'required|integer|min:1|max:3',
      ]);

      $entrepreneurship = Entrepreneurship::find($id);
      $newState = $request->inspection_state;

      $entrepreneurship->inspection_state = $newState;
      $entrepreneurship->save();

      return response()->json([
        'code' => 200,
        'message' => 'Entrepreneurship inspection state updated successfully',
        
        'entrepreneurship' => $entrepreneurship,
      ]);
    }

    public function destroy($id)
    {
      $entrepreneurship = Entrepreneurship::find($id);
      $entrepreneurship->delete();

      return response()->json([
        'code' => 200,
        'status' => 'success',
        'message' => 'entrepreneurship deleted successfully',
        'entrepreneurship' => $entrepreneurship,
      ]);
    }
}
