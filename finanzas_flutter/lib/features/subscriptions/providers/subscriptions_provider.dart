import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../data/subscriptions_repository.dart';
import '../models/subscription.dart';

class SubscriptionsNotifier extends AsyncNotifier<List<Subscription>> {
  final _repo = SubscriptionsRepository();

  @override
  Future<List<Subscription>> build() => _repo.getAll();

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

final subscriptionsProvider =
    AsyncNotifierProvider<SubscriptionsNotifier, List<Subscription>>(
        SubscriptionsNotifier.new);
