import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants.dart';
import '../../../shared/widgets/confirm_dialog.dart';
import '../../../shared/widgets/empty_state.dart';
import '../providers/categories_provider.dart';

class CategoriesListScreen extends ConsumerWidget {
  const CategoriesListScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(categoriesProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Categorías')),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          await context.push('/categories/add');
          ref.read(categoriesProvider.notifier).refresh();
        },
        child: const Icon(Icons.add),
      ),
      body: state.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => ErrorState(
          message: e.toString(),
          onRetry: () => ref.read(categoriesProvider.notifier).refresh(),
        ),
        data: (cats) => RefreshIndicator(
          onRefresh: () => ref.read(categoriesProvider.notifier).refresh(),
          child: cats.isEmpty
              ? const EmptyState(
                  message: 'Sin categorías',
                  icon: Icons.label_outline)
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
                  itemCount: cats.length,
                  itemBuilder: (ctx, i) {
                    final cat = cats[i];
                    final color =
                        cat.isIncome ? AppColors.income : AppColors.expense;
                    return Card(
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: color.withOpacity(0.12),
                          child: Icon(cat.isIncome
                              ? Icons.arrow_downward
                              : Icons.arrow_upward,
                              color: color, size: 18),
                        ),
                        title: Text(cat.name,
                            style: const TextStyle(fontWeight: FontWeight.w600)),
                        subtitle: Text(
                          cat.isIncome ? 'Ingreso' : 'Gasto',
                          style: const TextStyle(
                              color: AppColors.textSecondary, fontSize: 12),
                        ),
                        trailing: PopupMenuButton<String>(
                          onSelected: (action) async {
                            if (action == 'edit') {
                              await context.push('/categories/${cat.id}/edit');
                              ref.read(categoriesProvider.notifier).refresh();
                            } else if (action == 'delete') {
                              final ok = await showConfirmDialog(
                                context,
                                title: 'Eliminar categoría',
                                message:
                                    '¿Eliminar "${cat.name}"?',
                              );
                              if (ok) {
                                await ref
                                    .read(categoriesProvider.notifier)
                                    .delete(cat.id);
                              }
                            }
                          },
                          itemBuilder: (_) => const [
                            PopupMenuItem(value: 'edit', child: Text('Editar')),
                            PopupMenuItem(
                                value: 'delete',
                                child: Text('Eliminar',
                                    style: TextStyle(color: AppColors.expense))),
                          ],
                        ),
                      ),
                    );
                  },
                ),
        ),
      ),
    );
  }
}
