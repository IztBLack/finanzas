import 'package:dio/dio.dart';
import '../../../core/dio_client.dart';
import '../../../core/storage_service.dart';

class AuthRepository {
  final _dio = DioClient.get();

  /// POST /api/auth/login → devuelve { token, user }
  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final res = await _dio.post('/auth/login', data: {
        'email': email.trim(),
        'password': password,
        'device_name': 'Flutter Android',
      });
      final data = (res.data as Map<String, dynamic>)['data'] as Map<String, dynamic>;
      return data;
    } on DioException catch (e) {
      throw parseError(e);
    }
  }

  /// POST /api/auth/logout → revoca el token actual
  Future<void> logout() async {
    try {
      await _dio.post('/auth/logout');
    } on DioException catch (e) {
      // Ignorar errores de red al hacer logout
      if (e.response?.statusCode != 401) rethrow;
    } finally {
      await StorageService.clear();
      DioClient.reset();
    }
  }
}
