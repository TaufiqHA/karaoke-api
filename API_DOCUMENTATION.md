# Dokumentasi API Backend (Laravel Sanctum) & Panduan Integrasi Flutter

Dokumentasi ini menyediakan spesifikasi teknis endpoint API autentikasi dan master kategori lagu (`tb_songcategory`) serta panduan lengkap dan contoh kode Dart/Flutter untuk mengintegrasikan backend ini ke dalam aplikasi Flutter Anda.

---

## 1. Konfigurasi Server & Lingkungan

Saat menjalankan backend Laravel untuk pengembangan Flutter, pastikan server dapat diakses oleh emulator atau perangkat fisik Anda:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### Penentuan Base URL di Flutter
| Lingkungan / Device | Base URL |
| :--- | :--- |
| **Android Emulator** | `http://10.0.2.2:8000/api` |
| **iOS Simulator** | `http://127.0.0.1:8000/api` atau `http://localhost:8000/api` |
| **Real Device (Android/iOS via WiFi)** | `http://<IP_KOMPUTER_ANDA>:8000/api` *(contoh: `http://192.168.1.10:8000/api`)* |
| **Flutter Web / Desktop** | `http://localhost:8000/api` |

### Header Standar (Wajib)
Semua request ke API **harus menyertakan header** berikut:
```http
Accept: application/json
Content-Type: application/json
```
Untuk endpoint yang membutuhkan autentikasi (dilindungi `auth:sanctum`), tambahkan header:
```http
Authorization: Bearer <access_token>
```

---

## 2. Spesifikasi Endpoint

### A. Login
Digunakan untuk mengautentikasi pengguna menggunakan **username** dan **password**.

- **URL**: `/login`
- **Method**: `POST`
- **Autentikasi**: Tidak ada (Publik)

#### Request Body
```json
{
  "username": "testuser",
  "password": "password"
}
```

#### Respons Berhasil (200 OK)
```json
{
  "message": "Login successful",
  "access_token": "1|AbCdEf1234567890...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Test User",
    "username": "testuser",
    "email": "test@example.com",
    "role": "user",
    "email_verified_at": "2026-08-30T07:28:55.000000Z",
    "created_at": "2026-08-30T07:28:55.000000Z",
    "updated_at": "2026-08-30T07:28:55.000000Z"
  }
}
```

#### Respons Kredensial Salah (401 Unauthorized)
```json
{
  "message": "Invalid credentials"
}
```

#### Respons Validasi Gagal (422 Unprocessable Entity)
```json
{
  "message": "The username field is required. (and 1 more error)",
  "errors": {
    "username": [
      "The username field is required."
    ],
    "password": [
      "The password field is required."
    ]
  }
}
```

---

### B. Profil Pengguna (Me)
Mengambil data detail profil pengguna yang sedang login berdasarkan token akses.

- **URL**: `/me`
- **Method**: `GET`
- **Autentikasi**: `Bearer <access_token>`

#### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
```

#### Respons Berhasil (200 OK)
```json
{
  "user": {
    "id": 1,
    "name": "Test User",
    "username": "testuser",
    "email": "test@example.com",
    "role": "user",
    "email_verified_at": "2026-08-30T07:28:55.000000Z",
    "created_at": "2026-08-30T07:28:55.000000Z",
    "updated_at": "2026-08-30T07:28:55.000000Z"
  }
}
```

#### Respons Gagal / Belum Login (401 Unauthorized)
```json
{
  "message": "Unauthenticated."
}
```

---

### C. Logout
Mencabut / menghapus token akses yang sedang digunakan saat ini.

- **URL**: `/logout`
- **Method**: `POST`
- **Autentikasi**: `Bearer <access_token>`

#### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
```

#### Respons Berhasil (200 OK)
```json
{
  "message": "Successfully logged out"
}
```

#### Respons Gagal / Token Tidak Valid (401 Unauthorized)
```json
{
  "message": "Unauthenticated."
}
```

---

### D. Kelola Data Profil (Update Profile)
Memperbarui informasi nama, username, dan email pengguna yang sedang login.

- **URL**: `/profile`
- **Method**: `PUT` atau `PATCH`
- **Autentikasi**: `Bearer <access_token>`

#### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
Content-Type: application/json
```

#### Request Body
```json
{
  "name": "Nama Baru",
  "username": "usernamebaru",
  "email": "emailbaru@example.com"
}
```

#### Respons Berhasil (200 OK)
```json
{
  "message": "Profile updated successfully",
  "user": {
    "id": 1,
    "name": "Nama Baru",
    "username": "usernamebaru",
    "email": "emailbaru@example.com",
    "role": "user",
    "email_verified_at": "2026-08-30T07:28:55.000000Z",
    "created_at": "2026-08-30T07:28:55.000000Z",
    "updated_at": "2026-08-30T08:15:00.000000Z"
  }
}
```

#### Respons Validasi Gagal (422 Unprocessable Entity)
*(Misalnya username atau email sudah digunakan akun lain)*
```json
{
  "message": "The username has already been taken. (and 1 more error)",
  "errors": {
    "username": [
      "The username has already been taken."
    ]
  }
}
```

---

### E. Ubah Password (Update Password)
Mengubah password pengguna dengan mewajibkan verifikasi password lama (`current_password`).

- **URL**: `/profile/password`
- **Method**: `PUT`
- **Autentikasi**: `Bearer <access_token>`

#### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
Content-Type: application/json
```

#### Request Body
```json
{
  "current_password": "passwordlama123",
  "password": "passwordbaru123",
  "password_confirmation": "passwordbaru123"
}
```

#### Respons Berhasil (200 OK)
```json
{
  "message": "Password updated successfully"
}
```

#### Respons Validasi Gagal (422 Unprocessable Entity)
*(Misalnya current_password salah atau konfirmasi tidak cocok)*
```json
{
  "message": "The password is incorrect.",
  "errors": {
    "current_password": [
      "The password is incorrect."
    ]
  }
}
```

---

### F. Master Kategori Lagu (Song Categories)

Mengelola data kategori atau genre lagu (`tb_songcategory`). Endpoint pembacaan data bersifat publik, sedangkan operasi penambahan, perubahan, dan penghapusan memerlukan otentikasi Sanctum (`Bearer <access_token>`).

---

#### 1. Daftar Kategori Lagu (List Categories)
Mengambil seluruh daftar kategori lagu, mendukung pencarian nama kategori.

- **URL**: `/categories`
- **Method**: `GET`
- **Autentikasi**: Tidak ada (Publik)
- **Query Parameter (Opsional)**:
  - `search` (string): Mencari kategori berdasarkan kecocokan nama (case-insensitive `LIKE %search%`). Contoh: `/categories?search=Pop`

##### Request Header
```http
Accept: application/json
```

##### Respons Berhasil (200 OK)
```json
{
  "data": [
    {
      "songcategoryid": 1,
      "songcategoryname": "Dangdut",
      "created_at": "2026-08-30T08:23:25.000000Z",
      "updated_at": "2026-08-30T08:23:25.000000Z"
    },
    {
      "songcategoryid": 2,
      "songcategoryname": "Pop Indonesia",
      "created_at": "2026-08-30T08:23:25.000000Z",
      "updated_at": "2026-08-30T08:23:25.000000Z"
    }
  ]
}
```

---

#### 2. Detail Kategori Lagu (Show Category)
Mengambil informasi detail satu kategori lagu berdasarkan `songcategoryid`.

- **URL**: `/categories/{id}` *(contoh: `/categories/1`)*
- **Method**: `GET`
- **Autentikasi**: Tidak ada (Publik)

##### Request Header
```http
Accept: application/json
```

##### Respons Berhasil (200 OK)
```json
{
  "data": {
    "songcategoryid": 1,
    "songcategoryname": "Dangdut",
    "created_at": "2026-08-30T08:23:25.000000Z",
    "updated_at": "2026-08-30T08:23:25.000000Z"
  }
}
```

##### Respons Data Tidak Ditemukan (404 Not Found)
```json
{
  "message": "No query results for model [App\\Models\\Category] 999"
}
```

---

#### 3. Tambah Kategori Lagu (Create Category)
Menambahkan data kategori atau genre lagu baru.

- **URL**: `/categories`
- **Method**: `POST`
- **Autentikasi**: `Bearer <access_token>`

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
Content-Type: application/json
```

##### Request Body
| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `songcategoryname` | String | Ya | Nama kategori/genre lagu, maksimal 255 karakter, harus unik. |

```json
{
  "songcategoryname": "Jazz"
}
```

##### Respons Berhasil (201 Created)
```json
{
  "message": "Category created successfully",
  "data": {
    "songcategoryid": 3,
    "songcategoryname": "Jazz",
    "created_at": "2026-08-30T08:35:00.000000Z",
    "updated_at": "2026-08-30T08:35:00.000000Z"
  }
}
```

##### Respons Validasi Gagal (422 Unprocessable Entity)
*(Misalnya nama kategori kosong atau sudah pernah terdaftar)*
```json
{
  "message": "The songcategoryname has already been taken.",
  "errors": {
    "songcategoryname": [
      "The songcategoryname has already been taken."
    ]
  }
}
```

##### Respons Belum Login (401 Unauthorized)
```json
{
  "message": "Unauthenticated."
}
```

---

#### 4. Perbarui Kategori Lagu (Update Category)
Memperbarui nama kategori lagu yang sudah ada.

- **URL**: `/categories/{id}` *(contoh: `/categories/3`)*
- **Method**: `PUT` atau `PATCH`
- **Autentikasi**: `Bearer <access_token>`

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
Content-Type: application/json
```

##### Request Body
```json
{
  "songcategoryname": "Smooth Jazz"
}
```

##### Respons Berhasil (200 OK)
```json
{
  "message": "Category updated successfully",
  "data": {
    "songcategoryid": 3,
    "songcategoryname": "Smooth Jazz",
    "created_at": "2026-08-30T08:35:00.000000Z",
    "updated_at": "2026-08-30T08:40:00.000000Z"
  }
}
```

##### Respons Validasi Gagal (422 Unprocessable Entity)
```json
{
  "message": "The songcategoryname has already been taken.",
  "errors": {
    "songcategoryname": [
      "The songcategoryname has already been taken."
    ]
  }
}
```

##### Respons Tidak Ditemukan (404 Not Found)
```json
{
  "message": "No query results for model [App\\Models\\Category] 999"
}
```

---

#### 5. Hapus Kategori Lagu (Delete Category)
Menghapus data kategori lagu dari sistem.

- **URL**: `/categories/{id}` *(contoh: `/categories/3`)*
- **Method**: `DELETE`
- **Autentikasi**: `Bearer <access_token>`

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
```

##### Respons Berhasil (200 OK)
```json
{
  "message": "Category deleted successfully"
}
```

##### Respons Tidak Ditemukan (404 Not Found)
```json
{
  "message": "No query results for model [App\\Models\\Category] 999"
}
```

##### Respons Belum Login (401 Unauthorized)
```json
{
  "message": "Unauthenticated."
}
```

---

## 3. Contoh Implementasi di Flutter (Dart)

Berikut adalah contoh implementasi lengkap yang dapat langsung Anda gunakan pada project Flutter.

### Dependensi yang Disarankan
Tambahkan di `pubspec.yaml`:
```yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^1.2.0
  flutter_secure_storage: ^9.0.0 # Untuk menyimpan token secara aman
```

---

### A. Model Data (`lib/models/user_model.dart`)

```dart
class UserModel {
  final int id;
  final String name;
  final String username;
  final String email;
  final String role; // 'admin' atau 'user'
  final DateTime? emailVerifiedAt;
  final DateTime createdAt;
  final DateTime updatedAt;

  UserModel({
    required this.id,
    required this.name,
    required this.username,
    required this.email,
    required this.role,
    this.emailVerifiedAt,
    required this.createdAt,
    required this.updatedAt,
  });

  bool get isAdmin => role == 'admin';
  bool get isUser => role == 'user';

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] as int,
      name: json['name'] as String,
      username: json['username'] as String,
      email: json['email'] as String,
      role: json['role'] as String? ?? 'user',
      emailVerifiedAt: json['email_verified_at'] != null
          ? DateTime.parse(json['email_verified_at'] as String)
          : null,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'username': username,
      'email': email,
      'role': role,
      'email_verified_at': emailVerifiedAt?.toIso8601String(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
}

class AuthResponse {
  final String message;
  final String accessToken;
  final String tokenType;
  final UserModel user;

  AuthResponse({
    required this.message,
    required this.accessToken,
    required this.tokenType,
    required this.user,
  });

  factory AuthResponse.fromJson(Map<String, dynamic> json) {
    return AuthResponse(
      message: json['message'] as String,
      accessToken: json['access_token'] as String,
      tokenType: json['token_type'] as String,
      user: UserModel.fromJson(json['user'] as Map<String, dynamic>),
    );
  }
}

// File: lib/models/category_model.dart
class CategoryModel {
  final int songcategoryid;
  final String songcategoryname;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  CategoryModel({
    required this.songcategoryid,
    required this.songcategoryname,
    this.createdAt,
    this.updatedAt,
  });

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      songcategoryid: json['songcategoryid'] as int,
      songcategoryname: json['songcategoryname'] as String,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'] as String)
          : null,
      updatedAt: json['updated_at'] != null
          ? DateTime.parse(json['updated_at'] as String)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'songcategoryid': songcategoryid,
      'songcategoryname': songcategoryname,
      'created_at': createdAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }
}
```

---

### B. Service Autentikasi (`lib/services/auth_service.dart`)

```dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../models/user_model.dart';

class AuthService {
  // Tentukan base URL sesuai platform
  static String get baseUrl {
    if (Platform.isAndroid) {
      return 'http://10.0.2.2:8000/api'; // Android Emulator
    } else {
      return 'http://127.0.0.1:8000/api'; // iOS Simulator / Desktop / Web
    }
    // Jika menggunakan perangkat fisik Android/iOS, ganti dengan:
    // return 'http://192.168.1.xxx:8000/api';
  }

  final _storage = const FlutterSecureStorage();
  static const _tokenKey = 'auth_token';

  // Menyimpan token ke penyimpanan aman
  Future<void> saveToken(String token) async {
    await _storage.write(key: _tokenKey, value: token);
  }

  // Mengambil token
  Future<String?> getToken() async {
    return await _storage.read(key: _tokenKey);
  }

  // Menghapus token
  Future<void> deleteToken() async {
    await _storage.delete(key: _tokenKey);
  }

  // 1. Fungsi Login (menggunakan username & password)
  Future<AuthResponse> login({
    required String username,
    required String password,
  }) async {
    final url = Uri.parse('$baseUrl/login');
    final response = await http.post(
      url,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'username': username,
        'password': password,
      }),
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      final authResponse = AuthResponse.fromJson(data);
      await saveToken(authResponse.accessToken);
      return authResponse;
    } else if (response.statusCode == 401) {
      throw Exception(data['message'] ?? 'Username atau password salah.');
    } else if (response.statusCode == 422) {
      final errors = data['errors'] as Map<String, dynamic>?;
      final firstError = errors?.values.first?[0] ?? 'Validasi gagal.';
      throw Exception(firstError);
    } else {
      throw Exception('Terjadi kesalahan pada server (${response.statusCode}).');
    }
  }

  // 2. Fungsi Get Profile (Me)
  Future<UserModel> getProfile() async {
    final token = await getToken();
    if (token == null) {
      throw Exception('Token tidak ditemukan. Silakan login kembali.');
    }

    final url = Uri.parse('$baseUrl/me');
    final response = await http.get(
      url,
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      return UserModel.fromJson(data['user'] as Map<String, dynamic>);
    } else if (response.statusCode == 401) {
      await deleteToken();
      throw Exception('Sesi login telah kedaluwarsa.');
    } else {
      throw Exception('Gagal mengambil data profil.');
    }
  }

  // 3. Fungsi Logout
  Future<void> logout() async {
    final token = await getToken();
    if (token != null) {
      try {
        final url = Uri.parse('$baseUrl/logout');
        await http.post(
          url,
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer $token',
          },
        );
      } catch (e) {
        // Tangani jika terjadi error koneksi saat logout
      } finally {
        await deleteToken();
      }
    }
  }

  // 4. Fungsi Update Profile (Nama, Username, Email)
  Future<UserModel> updateProfile({
    required String name,
    required String username,
    required String email,
  }) async {
    final token = await getToken();
    if (token == null) {
      throw Exception('Token tidak ditemukan. Silakan login kembali.');
    }

    final url = Uri.parse('$baseUrl/profile');
    final response = await http.put(
      url,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({
        'name': name,
        'username': username,
        'email': email,
      }),
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      return UserModel.fromJson(data['user'] as Map<String, dynamic>);
    } else if (response.statusCode == 422) {
      final errors = data['errors'] as Map<String, dynamic>?;
      final firstError = errors?.values.first?[0] ?? 'Gagal memperbarui profil.';
      throw Exception(firstError);
    } else if (response.statusCode == 401) {
      await deleteToken();
      throw Exception('Sesi login telah kedaluwarsa.');
    } else {
      throw Exception('Gagal memperbarui profil (${response.statusCode}).');
    }
  }

  // 5. Fungsi Update Password
  Future<void> updatePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  }) async {
    final token = await getToken();
    if (token == null) {
      throw Exception('Token tidak ditemukan. Silakan login kembali.');
    }

    final url = Uri.parse('$baseUrl/profile/password');
    final response = await http.put(
      url,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({
        'current_password': currentPassword,
        'password': newPassword,
        'password_confirmation': newPasswordConfirmation,
      }),
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      return;
    } else if (response.statusCode == 422) {
      final errors = data['errors'] as Map<String, dynamic>?;
      final firstError = errors?.values.first?[0] ?? 'Password lama salah atau konfirmasi tidak cocok.';
      throw Exception(firstError);
    } else if (response.statusCode == 401) {
      await deleteToken();
      throw Exception('Sesi login telah kedaluwarsa.');
    } else {
      throw Exception('Gagal mengubah password (${response.statusCode}).');
    }
  }
}
```

---

### C. Service Kategori Lagu (`lib/services/category_service.dart`)

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'auth_service.dart';
import '../models/category_model.dart';

class CategoryService {
  final _authService = AuthService();
  final String baseUrl = AuthService.baseUrl;

  // 1. Ambil Semua Kategori (Mendukung filter search)
  Future<List<CategoryModel>> getCategories({String? search}) async {
    final query = (search != null && search.isNotEmpty)
        ? '?search=${Uri.encodeComponent(search)}'
        : '';
    final url = Uri.parse('$baseUrl/categories$query');

    final response = await http.get(
      url,
      headers: {
        'Accept': 'application/json',
      },
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      final list = data['data'] as List<dynamic>;
      return list
          .map((item) => CategoryModel.fromJson(item as Map<String, dynamic>))
          .toList();
    } else {
      throw Exception('Gagal memuat kategori lagu (${response.statusCode}).');
    }
  }

  // 2. Ambil Detail Kategori Berdasarkan ID
  Future<CategoryModel> getCategory(int id) async {
    final url = Uri.parse('$baseUrl/categories/$id');
    final response = await http.get(
      url,
      headers: {
        'Accept': 'application/json',
      },
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      return CategoryModel.fromJson(data['data'] as Map<String, dynamic>);
    } else if (response.statusCode == 404) {
      throw Exception('Kategori tidak ditemukan.');
    } else {
      throw Exception('Gagal memuat detail kategori.');
    }
  }

  // 3. Tambah Kategori Baru (Wajib Auth)
  Future<CategoryModel> createCategory(String name) async {
    final token = await _authService.getToken();
    if (token == null) {
      throw Exception('Silakan login terlebih dahulu.');
    }

    final url = Uri.parse('$baseUrl/categories');
    final response = await http.post(
      url,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({
        'songcategoryname': name,
      }),
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 201) {
      return CategoryModel.fromJson(data['data'] as Map<String, dynamic>);
    } else if (response.statusCode == 422) {
      final errors = data['errors'] as Map<String, dynamic>?;
      final firstError = errors?.values.first?[0] ?? 'Validasi gagal.';
      throw Exception(firstError);
    } else if (response.statusCode == 401) {
      throw Exception('Sesi login telah kedaluwarsa.');
    } else {
      throw Exception('Gagal menambahkan kategori.');
    }
  }

  // 4. Perbarui Kategori (Wajib Auth)
  Future<CategoryModel> updateCategory(int id, String name) async {
    final token = await _authService.getToken();
    if (token == null) {
      throw Exception('Silakan login terlebih dahulu.');
    }

    final url = Uri.parse('$baseUrl/categories/$id');
    final response = await http.put(
      url,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({
        'songcategoryname': name,
      }),
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      return CategoryModel.fromJson(data['data'] as Map<String, dynamic>);
    } else if (response.statusCode == 422) {
      final errors = data['errors'] as Map<String, dynamic>?;
      final firstError = errors?.values.first?[0] ?? 'Validasi gagal.';
      throw Exception(firstError);
    } else if (response.statusCode == 404) {
      throw Exception('Kategori tidak ditemukan.');
    } else if (response.statusCode == 401) {
      throw Exception('Sesi login telah kedaluwarsa.');
    } else {
      throw Exception('Gagal memperbarui kategori.');
    }
  }

  // 5. Hapus Kategori (Wajib Auth)
  Future<void> deleteCategory(int id) async {
    final token = await _authService.getToken();
    if (token == null) {
      throw Exception('Silakan login terlebih dahulu.');
    }

    final url = Uri.parse('$baseUrl/categories/$id');
    final response = await http.delete(
      url,
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      return;
    } else if (response.statusCode == 404) {
      throw Exception('Kategori tidak ditemukan.');
    } else if (response.statusCode == 401) {
      throw Exception('Sesi login telah kedaluwarsa.');
    } else {
      throw Exception('Gagal menghapus kategori.');
    }
  }
}
```

---

### D. Contoh Pemanggilan di Controller / UI Flutter

#### 1. Operasi Autentikasi & Akun
```dart
final authService = AuthService();

// Skenario 1: Proses Login
try {
  final result = await authService.login(
    username: usernameController.text.trim(),
    password: passwordController.text,
  );
  print('Selamat datang, ${result.user.name}!');
  // Navigasi ke halaman beranda
} catch (error) {
  print('Login gagal: $error');
}

// Skenario 2: Cek Sesi Pengguna saat App Dibuka (Splash / Init)
try {
  final user = await authService.getProfile();
  print('Sesi valid untuk user: ${user.username}');
  // Arahkan ke HomeScreen
} catch (error) {
  // Arahkan ke LoginScreen
}

// Skenario 3: Logout
await authService.logout();
// Arahkan kembali ke LoginScreen

// Skenario 4: Update Profil (Nama, Username, Email)
try {
  final updatedUser = await authService.updateProfile(
    name: 'Nama Pengguna Baru',
    username: 'usernambaru',
    email: 'emailbaru@example.com',
  );
  print('Profil berhasil diperbarui: ${updatedUser.name}');
} catch (error) {
  print('Gagal memperbarui profil: $error');
}

// Skenario 5: Ubah Password
try {
  await authService.updatePassword(
    currentPassword: 'passwordlama123',
    newPassword: 'passwordbaru123',
    newPasswordConfirmation: 'passwordbaru123',
  );
  print('Password berhasil diubah.');
} catch (error) {
  print('Gagal mengubah password: $error');
}
```

#### 2. Operasi Master Kategori Lagu
```dart
final categoryService = CategoryService();

// Skenario 1: Ambil Semua Kategori
try {
  final categories = await categoryService.getCategories();
  print('Total kategori: ${categories.length}');
} catch (error) {
  print('Error: $error');
}

// Skenario 2: Cari Kategori
try {
  final filtered = await categoryService.getCategories(search: 'Pop');
  print('Hasil pencarian: ${filtered.map((e) => e.songcategoryname).toList()}');
} catch (error) {
  print('Error: $error');
}

// Skenario 3: Tambah Kategori Baru (Perlu login)
try {
  final newCategory = await categoryService.createCategory('Dangdut Koplo');
  print('Kategori berhasil ditambahkan: ID ${newCategory.songcategoryid}');
} catch (error) {
  print('Gagal tambah: $error');
}

// Skenario 4: Update Kategori (Perlu login)
try {
  final updated = await categoryService.updateCategory(1, 'Dangdut Klasik');
  print('Nama baru: ${updated.songcategoryname}');
} catch (error) {
  print('Gagal update: $error');
}

// Skenario 5: Hapus Kategori (Perlu login)
try {
  await categoryService.deleteCategory(1);
  print('Kategori berhasil dihapus');
} catch (error) {
  print('Gagal hapus: $error');
}
```

---

## 4. Troubleshooting & Tips Integrasi

1. **Error `Connection refused`**:
   - Pastikan backend berjalan dengan opsi `--host=0.0.0.0` bukan hanya `127.0.0.1`.
   - Pastikan Anda menggunakan `10.0.2.2` jika menguji di Android Emulator.
2. **Error `Cleartext HTTP traffic not permitted` pada Android**:
   - Jika aplikasi dijalankan di Android dengan `http://` (non-HTTPS), pastikan konfigurasi `android:usesCleartextTraffic="true"` ditambahkan pada tag `<application>` di file `android/app/src/main/AndroidManifest.xml`.
3. **Pesan Validasi Berbahasa Lain**:
   - Pesan validasi default dikelola melalui Laravel Localization. Jika ingin mengubah bahasa pesan validasi, konfigurasi `APP_LOCALE` di file `.env`.
