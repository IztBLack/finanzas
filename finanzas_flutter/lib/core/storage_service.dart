import 'dart:convert';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'constants.dart';

/// Wrapper estático sobre flutter_secure_storage.
class StorageService {
  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );

  // ── Token ────────────────────────────────────────────────────────────
  static Future<void> saveToken(String token) =>
      _storage.write(key: AppConstants.tokenKey, value: token);

  static Future<String?> getToken() =>
      _storage.read(key: AppConstants.tokenKey);

  static Future<void> deleteToken() =>
      _storage.delete(key: AppConstants.tokenKey);

  // ── User ─────────────────────────────────────────────────────────────
  static Future<void> saveUser(Map<String, dynamic> user) =>
      _storage.write(key: AppConstants.userKey, value: jsonEncode(user));

  static Future<Map<String, dynamic>?> getUser() async {
    final raw = await _storage.read(key: AppConstants.userKey);
    if (raw == null) return null;
    return jsonDecode(raw) as Map<String, dynamic>;
  }

  static Future<void> deleteUser() =>
      _storage.delete(key: AppConstants.userKey);

  // ── Utilidad ──────────────────────────────────────────────────────────
  static Future<void> clear() => _storage.deleteAll();
}
