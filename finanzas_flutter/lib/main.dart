import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'app.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  // Carga los datos de locale para DateFormat (es_MX). Sin esto, formatear
  // fechas con locale explícito lanza LocaleDataException → pantalla blanca.
  await initializeDateFormatting('es_MX', null);
  runApp(const ProviderScope(child: MyApp()));
}
