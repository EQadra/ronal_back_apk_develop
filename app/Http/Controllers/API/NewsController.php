<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
   

    /**
     * 📰 Listar todas las noticias
     * Endpoint: GET /api/news
     */
    public function index()
    {
        try {
            $news = News::orderBy('fecha_publicacion', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Lista de noticias obtenida correctamente.',
                'data'    => $news,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de noticias.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 Crear una noticia
     * Endpoint: POST /api/news
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'titulo'            => 'required|string|max:255',
                'descripcion'       => 'required|string',
                'url'               => 'nullable|string|max:500',
                'fecha_publicacion' => 'nullable|date',
            ]);

            $news = News::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Noticia creada correctamente.',
                'data'    => $news,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la noticia.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔍 Mostrar una noticia específica
     * Endpoint: GET /api/news/{id}
     */
    public function show($id)
    {
        try {
            $news = News::find($id);

            if (!$news) {
                return response()->json([
                    'success' => false,
                    'message' => 'Noticia no encontrada.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detalle de la noticia obtenido correctamente.',
                'data'    => $news,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la noticia.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * ✏️ Actualizar una noticia
     * Endpoint: PUT /api/news/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $news = News::find($id);
            if (!$news) {
                return response()->json([
                    'success' => false,
                    'message' => 'Noticia no encontrada.',
                ], 404);
            }

            $validated = $request->validate([
                'titulo'            => 'sometimes|string|max:255',
                'descripcion'       => 'sometimes|string',
                'url'               => 'nullable|string|max:500',
                'fecha_publicacion' => 'nullable|date',
            ]);

            $news->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Noticia actualizada correctamente.',
                'data'    => $news,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la noticia.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * 🗑️ Eliminar una noticia
     * Endpoint: DELETE /api/news/{id}
     */
    public function destroy($id)
    {
        try {
            $news = News::find($id);
            if (!$news) {
                return response()->json([
                    'success' => false,
                    'message' => 'Noticia no encontrada.',
                ], 404);
            }

            $news->delete();

            return response()->json([
                'success' => true,
                'message' => 'Noticia eliminada correctamente.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la noticia.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }
}
