import 'package:flutter/material.dart';

// ── API ────────────────────────────────────────────────────────────────────
class AppConstants {
  /// URL base del backend PHP.
  /// En producción apuntará al dominio real; en dev a la IP LAN.
  static const String baseUrl =
      'http://192.168.1.97/Finanzas/public/api';

  // Claves de almacenamiento seguro
  static const String tokenKey = 'finanzas_token';
  static const String userKey  = 'finanzas_user';
}

// ── Colores ────────────────────────────────────────────────────────────────
class AppColors {
  static const Color primary    = Color(0xFF1565C0);
  static const Color primaryLight = Color(0xFF1E88E5);
  static const Color background = Color(0xFF0A0D14);
  static const Color surface    = Color(0xFF141824);
  static const Color card       = Color(0xFF1C2132);
  static const Color cardBorder = Color(0xFF262D42);
  static const Color income     = Color(0xFF00BFA5);
  static const Color expense    = Color(0xFFEF5350);
  static const Color textPrimary   = Color(0xFFE8EAF0);
  static const Color textSecondary = Color(0xFF8A93A8);
  static const Color divider    = Color(0xFF262D42);

  /// Color neutral para estados pausados/inactivos (suscripciones, etc.)
  static const Color neutral    = Color(0xFF8A93A8);
  /// Color de advertencia (saldos pendientes, alertas suaves).
  static const Color warning    = Color(0xFFFFB74D);
  /// Color base para shimmer de skeletons.
  static const Color skeleton   = Color(0xFF232940);
}
