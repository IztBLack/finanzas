import 'package:dio/dio.dart';
import '../../../core/dio_client.dart';
import '../models/account.dart';

class AccountsRepository {
  final _dio = DioClient.get();

  Future<List<Account>> getAll() async {
    try {
      final res = await _dio.get('/accounts');
      final list = parseData(res) as List;
      return list.map((e) => Account.fromJson(e as Map<String, dynamic>)).toList();
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<Account> getById(int id) async {
    try {
      final res = await _dio.get('/accounts/$id');
      return Account.fromJson(parseData(res) as Map<String, dynamic>);
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> create(Map<String, dynamic> data) async {
    try {
      await _dio.post('/accounts', data: data);
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> update(int id, Map<String, dynamic> data) async {
    try {
      await _dio.put('/accounts/$id', data: data);
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> delete(int id) async {
    try {
      await _dio.delete('/accounts/$id');
    } on DioException catch (e) { throw parseError(e); }
  }
}
