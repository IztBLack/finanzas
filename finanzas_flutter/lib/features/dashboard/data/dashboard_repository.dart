import 'package:dio/dio.dart';
import '../../../core/dio_client.dart';
import '../models/dashboard_data.dart';

class DashboardRepository {
  final _dio = DioClient.get();

  Future<DashboardData> getDashboard() async {
    try {
      final res = await _dio.get('/dashboard');
      final data = parseData(res) as Map<String, dynamic>;
      return DashboardData.fromJson(data);
    } on DioException catch (e) {
      throw parseError(e);
    }
  }
}
