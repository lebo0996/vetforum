<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use App\Models\Badge;
use App\Notifications\NewCommentNotification;
use App\Notifications\NewLikeNotification;

class ForumController extends Controller
{
    public function home()
    {
        $categories = Category::withCount('posts')->get();
        $recentPosts = Post::with(['user', 'category'])->where('is_approved', true)->latest()->take(5)->get();
        return view('home', compact('categories', 'recentPosts'));
    }

    public function index(Request $request)
    {
        $categories = Category::withCount('posts')->get();
        $query = Post::with(['user', 'category'])->withCount('comments')->where('is_approved', true);

        if ($request->filled('categoria')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->categoria));
        }

        if ($request->filled('buscar')) {
            $query->where('title', 'like', '%' . $request->buscar . '%');
        }

        $posts = $query->orderByDesc('is_pinned')->latest()->paginate(12);
        return view('forum.index', compact('categories', 'posts'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('forum.create', compact('categories'));
    }

    public function show(string $slug)
    {
        /** @var Post $post */
        $post = Post::with([
            'user.followers', 
            'user.following', 
            'category', 
            'comments.user.followers', 
            'likes'
        ])->where('slug', $slug)->firstOrFail();

        return view('forum.show', compact('post'));
    }

    public function storePost(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        // Sanitización y filtro de palabras
        $contenidoLimpio = $this->sanitizarTexto($data['content']);

        $post = Post::create([
            'user_id' => Auth::id(),
            'category_id' => $data['category_id'],
            'title' => strip_tags($data['title']),
            'slug' => Str::slug($data['title']) . '-' . Str::random(5),
            'content' => $contenidoLimpio,
            'image' => $imagePath,
            'is_approved' => true,
        ]);

        // Revisar si ganó insignias por cantidad de temas creados en el foro
        /** @var User $user */
        $user = $request->user();
        $this->revisarInsigniasPosts($user);

        return redirect()->route('forum.show', $post->slug)->with('success', 'Publicación creada correctamente.');
    }

    public function storeComment(Request $request, Post $post)
    {
        $data = $request->validate(['content' => 'required|string']);

        // Sanitización y filtro de palabras
        $contenidoLimpio = $this->sanitizarTexto($data['content']);

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'content' => $contenidoLimpio,
        ]);
        
        if ($post->user_id !== Auth::id()) {
            $post->user->notify(new NewCommentNotification($comment));
        }
        
        return back()->with('success', 'Comentario publicado.');
    }

    public function toggleLike(Post $post)
    {
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->id;

        $existingLike = $post->likes()->where('user_id', $userId)->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            $post->likes()->create(['user_id' => $userId]);
            
            if ($post->user_id !== $userId) {
                $post->user->notify(new NewLikeNotification($post, $user));
            }
        }

        return back();
    }

    public function edit(\App\Models\Post $post)
    {
        /** @var \App\Models\User $user */
        $user = request()->user();

        if ($user->id !== $post->user_id && $user->role !== 'admin') {
            abort(403, 'No tienes permiso para editar esta publicación.');
        }

        $categories = Category::orderBy('name')->get();
        return view('forum.edit', compact('post', 'categories'));
    }

    public function update(Request $request, \App\Models\Post $post)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->id !== $post->user_id && $user->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_image' => 'nullable|boolean',
        ]);

        $post->title = strip_tags($request->title);
        $post->content = $this->sanitizarTexto($request->content);
        $post->category_id = $request->category_id;
        $post->slug = Str::slug($request->title) . '-' . $post->id; 

        if ($request->has('remove_image') && $request->remove_image == '1') {
            if ($post->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($post->image);
            }
            $post->image = null;
        }

        if ($request->hasFile('image')) {
            if ($post->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($post->image);
            }
            $path = $request->file('image')->store('posts', 'public');
            $post->image = $path;
        }
        
        $post->save();

        return redirect()->route('forum.show', $post->slug)->with('success', 'Publicación actualizada correctamente.');
    }

    /**
     * Limpia scripts y etiquetas peligrosas de Quill.js y censura palabras prohibidas.
     */
    private function sanitizarTexto(string $html): string
    {
        // 1. Solo permite tags estructurales inofensivas del editor
        $tagsPermitidas = '<p><br><strong><b><em><i><u><ul><ol><li><a><blockquote><h1><h2><h3>';
        $seguro = strip_tags($html, $tagsPermitidas);

        // 2. Filtro de malas palabras (puedes ampliar esta lista)
        $malasPalabras = [
            'puto', 'puta', 'mierda', 'verga', 'pendejo', 'pendeja', 
            'estupido', 'estupida', 'idiota', 'cabron', 'cabrona'
        ];

        return str_ireplace($malasPalabras, '***', $seguro);
    }

    /**
     * Evalúa y asigna insignias automáticas ganadas por cantidad de posts en el foro
     */
    private function revisarInsigniasPosts(User $user): void
    {
        $totalPosts = $user->posts()->count();

        $insigniasGanadas = Badge::where('requirement_type', 'posts')
                                 ->where('requirement_goal', '<=', $totalPosts)
                                 ->get();

        foreach ($insigniasGanadas as $badge) {
            if (!$user->badges->contains($badge->id)) {
                $user->badges()->attach($badge->id);
            }
        }
    }
}