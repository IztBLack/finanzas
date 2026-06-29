import 'package:dio/dio.dart';
import '../../../core/dio_client.dart';
import '../models/loan.dart';

class LoansRepository {
  final _dio = DioClient.get();

  Future<List<Loan>> getAll() async {
    try {
      final res = await _dio.get('/loans');
      final list = parseData(res) as List;
      return list.map((e) => Loan.fromJson(e as Map<String, dynamic>)).toList();
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<Loan> getById(int id) async {
    try {
      final res = await _dio.get('/loans/$id');
      return Loan.fromJson(parseData(res) as Map<String, dynamic>);
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> create(Map<String, dynamic> data) async {
    try {
      await _dio.post('/loans', data: data);
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> update(int id, Map<String, dynamic> data) async {
    try {
      await _dio.put('/loans/$id', data: data);
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> delete(int id) async {
    try {
      await _dio.delete('/loans/$id');
    } on DioException catch (e) { throw parseError(e); }
  }

  // ── Pagos ─────────────────────────────────────────────────────────────
  Future<List<LoanPayment>> getPayments(int loanId) async {
    try {
      final res = await _dio.get('/loans/$loanId/payments');
      final list = parseData(res) as List;
      return list.map((e) => LoanPayment.fromJson(e as Map<String, dynamic>)).toList();
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> addPayment(int loanId, Map<String, dynamic> data) async {
    try {
      await _dio.post('/loans/$loanId/payments', data: data);
    } on DioException catch (e) { throw parseError(e); }
  }

  Future<void> deletePayment(int paymentId) async {
    try {
      await _dio.delete('/loans/payments/$paymentId');
    } on DioException catch (e) { throw parseError(e); }
  }
}
