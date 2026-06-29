import 'package:dio/dio.dart';
import 'constants.dart';
import 'storage_service.dart';

/// Singleton de Dio con interceptor de token Bearer.
class DioClient {
  static Dio? _instance;

  static Dio get() {
    _instance ??= _build();
    return _instance!;
  }

  /// Llama a esto al hacer logout para limpiar el cliente.
  static void reset() => _instance = null;

  static Dio _build() {
    final dio = Dio(BaseOptions(
      baseUrl: AppConstants.baseUrl,
      connectTimeout: const Duration(seconds: 10),
      receiveTimeout: const Duration(seconds: 20),
      contentType: 'application/json',
      responseType: ResponseType.json,
    ));

    // Interceptor: inyecta Bearer token en cada request
    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await StorageService.getToken();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        },
        onError: (DioException e, handler) {
          // Deja que los repositorios manejen los errores
          handler.next(e);
        },
      ),
    );

    return dio;
  }
}

/// Extrae el campo `data` de la respuesta estándar { success, data, message }.
dynamic parseData(Response response) {
  final body = response.data;
  if (body is Map<String, dynamic>) {
    return body['data'];
  }
  return body;
}

/// Lanza una excepción con el mensaje de error del backend.
String parseError(DioException e) {
  try {
    final body = e.response?.data;
    if (body is Map<String, dynamic>) {
      return body['error'] ?? body['message'] ?? 'Error desconocido.';
    }
  } catch (_) {}
  return e.message ?? 'Error de conexión.';
}
