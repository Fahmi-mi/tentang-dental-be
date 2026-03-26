<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Resources\Admin\AdminLoginResource;
use App\Http\Resources\Admin\AdminProfileResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminChangeEmailRequest;
use App\Http\Requests\AdminChangePasswordRequest;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminRegisterRequest;
use App\Http\Requests\AdminUpdateProfileRequest;
use App\Models\Admin;
use App\Helpers\FileHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(AdminRegisterRequest $request)
    {
        try {
            $admin = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'role' => 'registration',
                'profile_image' => null,
            ]);

            return response()->json(
                FileHelper::formatResponse(true, new AdminProfileResource($admin), 'Registrasi admin berhasil'),
                201
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function login(AdminLoginRequest $request)
    {
        try {
            $admin = Admin::where('email', $request->email)->first();

            if (!$admin || !Hash::check($request->password, $admin->password)) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Email atau password salah'),
                    401
                );
            }

            $token = $admin->createToken('admin-token')->plainTextToken;

            return response()->json(
                FileHelper::formatResponse(true, new AdminLoginResource([
                    'admin' => $admin,
                    'token' => $token,
                ]), 'Login berhasil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Logout berhasil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function refresh(Request $request)
    {
        try {
            $admin = $request->user();

            $request->user()->currentAccessToken()->delete();

            $newToken = $admin->createToken('admin-token')->plainTextToken;

            $data = [
                'token' => $newToken,
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Token berhasil di-refresh'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function me(Request $request)
    {
        try {
            $admin = $request->user();

            return response()->json(
                FileHelper::formatResponse(true, new AdminProfileResource($admin), 'Data admin berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function changeEmail(AdminChangeEmailRequest $request)
    {
        try {
            /** @var Admin $admin */
            $admin = $request->user();

            if (!Hash::check($request->current_password, $admin->password)) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Password saat ini tidak sesuai'),
                    422
                );
            }

            $admin->email = $request->new_email;
            $admin->save();

            return response()->json(
                FileHelper::formatResponse(true, new AdminProfileResource($admin), 'Email berhasil diubah'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function changePassword(AdminChangePasswordRequest $request)
    {
        try {
            /** @var Admin $admin */
            $admin = $request->user();

            if (!Hash::check($request->current_password, $admin->password)) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Password saat ini tidak sesuai'),
                    422
                );
            }

            $admin->password = $request->new_password;
            $admin->save();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Password berhasil diubah'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function updateProfile(AdminUpdateProfileRequest $request)
    {
        try {
            /** @var Admin $admin */
            $admin = $request->user();

            if ($request->filled('name')) {
                $admin->name = $request->name;
            }

            if ($request->hasFile('profile_image')) {
                $newImage = FileHelper::uploadImage($request->file('profile_image'), 'admins');

                if (!$newImage) {
                    return response()->json(
                        FileHelper::formatResponse(false, null, 'Gagal upload foto profil'),
                        500
                    );
                }

                if ($admin->profile_image) {
                    FileHelper::deleteImage('admins/' . $admin->profile_image);
                }

                $admin->profile_image = $newImage;
            }

            $admin->save();

            return response()->json(
                FileHelper::formatResponse(true, new AdminProfileResource($admin), 'Profil berhasil diperbarui'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }
}
