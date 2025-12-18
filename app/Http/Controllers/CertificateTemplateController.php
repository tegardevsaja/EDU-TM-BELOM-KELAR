<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CertificateTemplateController extends Controller
{
    public function index()
    {
        $templates = CertificateTemplate::all();
        $printedAll = 0;
        $printedThisMonth = 0;
        if (class_exists(\App\Models\CertificateHistory::class)) {
            $printedAll = (int) \App\Models\CertificateHistory::sum('jumlah_siswa');
            $printedThisMonth = (int) \App\Models\CertificateHistory::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('jumlah_siswa');
        }
        return view('master.sertifikat.index', compact('templates', 'printedAll', 'printedThisMonth'));
    }

    public function create()
    {
        return view('master.sertifikat.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_template' => 'required|string|max:255',
            'file_background' => 'required|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $file = $request->file('file_background');

        // Tentukan nama file target (default .webp)
        $ext = strtolower($file->getClientOriginalExtension());
        $isWebp = $ext === 'webp';
        $fileName = Str::uuid()->toString() . ($isWebp ? '.webp' : '.webp');
        $savePath = storage_path('app/public/certificate_templates/' . $fileName);

        // Pastikan folder ada
        if (!file_exists(storage_path('app/public/certificate_templates'))) {
            mkdir(storage_path('app/public/certificate_templates'), 0755, true);
        }

        if ($isWebp) {
            // Jika sudah WebP, simpan langsung tanpa re-encode
            $file->move(dirname($savePath), basename($savePath));
        } else {
            // Konversi ke WebP menggunakan Intervention Image (GD)
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);
            $image->toWebp(80)->save($savePath);
        }

        // Simpan path ke database
        CertificateTemplate::create([
            'nama_template' => $request->nama_template,
            'background_image' => 'certificate_templates/' . $fileName,
        ]);

        return redirect()
            ->route('master.sertifikat.index')
            ->with('success', 'Template berhasil ditambahkan dan dikonversi ke WebP!');
    }

    public function edit($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        return view('master.sertifikat.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_template' => 'required|string|max:255',
            'file_background' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $template = CertificateTemplate::findOrFail($id);

        // Update nama template
        $template->nama_template = $request->nama_template;

        // Jika ada file background baru
        if ($request->hasFile('file_background')) {
            // Hapus file lama jika ada
            if ($template->background_image && Storage::disk('public')->exists($template->background_image)) {
                Storage::disk('public')->delete($template->background_image);
            }

            $file = $request->file('file_background');

            // Buat nama file target (default .webp)
            $ext = strtolower($file->getClientOriginalExtension());
            $isWebp = $ext === 'webp';
            $fileName = Str::uuid()->toString() . ($isWebp ? '.webp' : '.webp');
            $savePath = storage_path('app/public/certificate_templates/' . $fileName);

            // Pastikan folder ada
            if (!file_exists(storage_path('app/public/certificate_templates'))) {
                mkdir(storage_path('app/public/certificate_templates'), 0755, true);
            }

            if ($isWebp) {
                // Jika sudah WebP, simpan langsung tanpa re-encode
                $file->move(dirname($savePath), basename($savePath));
            } else {
                // Konversi ke WebP menggunakan Intervention Image (GD)
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file);
                $image->toWebp(80)->save($savePath);
            }

            // Update path di database
            $template->background_image = 'certificate_templates/' . $fileName;
        }

        $template->save();

        return redirect()
            ->route('master.sertifikat.index')
            ->with('success', 'Template berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $template = CertificateTemplate::findOrFail($id);

        // Hapus file background jika ada
        if ($template->background_image && Storage::disk('public')->exists($template->background_image)) {
            Storage::disk('public')->delete($template->background_image);
        }

        // Hapus data dari database
        $template->delete();

        return redirect()
            ->route('master.sertifikat.index')
            ->with('success', 'Template berhasil dihapus!');
    }
}