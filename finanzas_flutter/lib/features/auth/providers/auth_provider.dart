import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Estado de autenticación del usuario.
class AuthState {
  final String token;
  final int    userId;
  final String name;
  final String email;

  const AuthState({
    required this.token,
    required this.userId,
    required this.name,
    required this.email,
  });

  factory AuthState.fromMap(String token, Map<String, dynamic> user) {
    return AuthState(
      token:  token,
      userId: (user['id'] as num).toInt(),
      name:   user['name'] as String,
      email:  user['email'] as String,
    );
  }
}

/// null → no autenticado; AuthState → autenticado.
final authProvider = StateProvider<AuthState?>((ref) => null);
