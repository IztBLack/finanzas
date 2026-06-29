import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../data/loans_repository.dart';
import '../models/loan.dart';

class LoansNotifier extends AsyncNotifier<List<Loan>> {
  final _repo = LoansRepository();

  @override
  Future<List<Loan>> build() => _repo.getAll();

  Future<void> refresh() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() => _repo.getAll());
  }

  Future<void> create(Map<String, dynamic> data) async {
    await _repo.create(data);
    await refresh();
  }

  Future<void> edit(int id, Map<String, dynamic> data) async {
    await _repo.update(id, data);
    await refresh();
  }

  Future<void> delete(int id) async {
    await _repo.delete(id);
    await refresh();
  }
}

final loansProvider =
    AsyncNotifierProvider<LoansNotifier, List<Loan>>(LoansNotifier.new);

// ── Pagos de un préstamo específico ──────────────────────────────────────
final loanPaymentsProvider = FutureProvider.autoDispose.family<List<LoanPayment>, int>(
  (ref, loanId) => LoansRepository().getPayments(loanId),
);
