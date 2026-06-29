import 'package:dio/dio.dart';
import '../../../core/dio_client.dart';
import '../models/transaction.dart';

class TransactionsRepository {
  final _dio = DioClient.get();

  Future<List<AppTransaction>> getAll() async {
    try {
      final res = await _dio.get('/transactions');
      final list = parseData(res) as List;
      return list.map((e) => AppTransaction.fromJson(e as Map<String, dynamic>)).toList();
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> create(Map<String, dynamic> data) async {
    try {
      await _dio.post('/transactions', data: data);
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> update(int id, Map<String, dynamic> data) async {
    try {
      await _dio.put('/transactions/$id', data: data);
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> delete(int id) async {
    try {
      await _dio.delete('/transactions/$id');
    } on DioException catch (e) { throw parseError(e); }
  }
}
