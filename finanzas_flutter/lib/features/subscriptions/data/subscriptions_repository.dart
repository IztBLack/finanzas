import 'package:dio/dio.dart';
import '../../../core/dio_client.dart';
import '../models/subscription.dart';

class SubscriptionsRepository {
  final _dio = DioClient.get();

  Future<List<Subscription>> getAll() async {
    try {
      final res = await _dio.get('/subscriptions');
      final list = parseData(res) as List;
      return list.map((e) => Subscription.fromJson(e as Map<String, dynamic>)).toList();
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> create(Map<String, dynamic> data) async {
    try {
      await _dio.post('/subscriptions', data: data);
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> update(int id, Map<String, dynamic> data) async {
    try {
      await _dio.put('/subscriptions/$id', data: data);
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> delete(int id) async {
    try {
      await _dio.delete('/subscriptions/$id');
    } on DioException catch (e) { throw parseError(e); }
  }
}
