import 'package:dio/dio.dart';
import '../../../core/dio_client.dart';
import '../models/category.dart';

class CategoriesRepository {
  final _dio = DioClient.get();

  Future<List<Category>> getAll() async {
    try {
      final res = await _dio.get('/categories');
      final list = parseData(res) as List;
      return list.map((e) => Category.fromJson(e as Map<String, dynamic>)).toList();
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> create(Map<String, dynamic> data) async {
    try {
      await _dio.post('/categories', data: data);
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> update(int id, Map<String, dynamic> data) async {
    try {
      await _dio.put('/categories/$id', data: data);
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> delete(int id) async {
    try {
      await _dio.delete('/categories/$id');
    } on DioException catch (e) { throw parseError(e); }
  }
}
