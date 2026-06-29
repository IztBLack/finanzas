import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../data/accounts_repository.dart';
import '../models/account.dart';

class AccountsNotifier extends AsyncNotifier<List<Account>> {
  final _repo = AccountsRepository();

  @override
  Future<List<Account>> build() => _repo.getAll();

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

final accountsProvider =
    AsyncNotifierProvider<AccountsNotifier, List<Account>>(AccountsNotifier.new);
