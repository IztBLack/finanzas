import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../data/categories_repository.dart';
import '../models/category.dart';

class CategoriesNotifier extends AsyncNotifier<List<Category>> {
  final _repo = CategoriesRepository();

  @override
  Future<List<Category>> build() => _repo.getAll();

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

final categoriesProvider =
    AsyncNotifierProvider<CategoriesNotifier, List<Category>>(CategoriesNotifier.new);
