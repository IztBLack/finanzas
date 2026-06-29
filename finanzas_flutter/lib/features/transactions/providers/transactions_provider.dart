import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../data/transactions_repository.dart';
import '../models/transaction.dart';

class TransactionsNotifier extends AsyncNotifier<List<AppTransaction>> {
  final _repo = TransactionsRepository();

  @override
  Future<List<AppTransaction>> build() => _repo.getAll();

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

final transactionsProvider =
    AsyncNotifierProvider<TransactionsNotifier, List<AppTransaction>>(
        TransactionsNotifier.new);
