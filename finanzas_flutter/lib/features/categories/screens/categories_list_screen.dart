import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants.dart';
import '../../../shared/widgets/confirm_dialog.dart';
import '../../../shared/widgets/empty_state.dart';
import '../../../shared/widgets/list_item_card.dart';
import '../../../shared/widgets/loading_state.dart';
import '../providers/categories_provider.dart';
import '../models/category.dart';

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
        tooltip: 'Nueva categoría',
        child: const Icon(Icons.add),
      ),
      body: state.when(
        loading: () => const SkeletonList(itemHeight: 64),
        error: (e, _) => ErrorState(
          message: e.toString(),
          onRetry: () => ref.read(categoriesProvider.notifier).refresh(),
        ),
        data: (cats) => RefreshIndicator(
          onRefresh: () => ref.read(categoriesProvider.notifier).refresh(),
          child: cats.isEmpty
              ? ListView(
                  children: [
                    SizedBox(
                      height: MediaQuery.of(context).size.height * 0.7,
                      child: EmptyState(
                        message: 'Sin categorías',
                        subtitle: 'Crea categorías para clasificar tus movimientos',
                        icon: Icons.label_outline,
                        actionLabel: 'Nueva categoría',
                        onAction: () async {
                          await context.push('/categories/add');
                          ref.read(categoriesProvider.notifier).refresh();
                        },
                      ),
                    ),
                  ],
                )
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
                  itemCount: cats.length,
                  itemBuilder: (ctx, i) {
                    final cat = cats[i];
                    return _CategoryTile(
                      category: cat,
                      onEdit: () async {
                        await context.push('/categories/${cat.id}/edit');
                        ref.read(categoriesProvider.notifier).refresh();
                      },
                      onDelete: () async {
                        final ok = await showConfirmDialog(
                          context,
                          title: 'Eliminar categoría',
                          message: '¿Eliminar "${cat.name}"?',
                        );
                        if (ok) {
                          await ref
                              .read(categoriesProvider.notifier)
                              .delete(cat.id);
                        }
                      },
                    );
                  },
                ),
        ),
      ),
    );
  }
}

class _CategoryTile extends StatelessWidget {
  final Category category;
  final VoidCallback onEdit;
  final VoidCallback onDelete;

  const _CategoryTile({
    required this.category,
    required this.onEdit,
    required this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    final color = category.isIncome ? AppColors.income : AppColors.expense;

    return ListItemCard(
      leading: CircleIconBadge(
        icon: category.isIncome ? Icons.arrow_downward : Icons.arrow_upward,
        color: color,
      ),
      title: Text(category.name,
          style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
          maxLines: 1,
          overflow: TextOverflow.ellipsis),
      subtitle: StatusBadgeText(
        label: category.isIncome ? 'Ingreso' : 'Gasto',
        color: color,
      ),
      trailing: PopupMenuButton<String>(
        icon: const Icon(Icons.more_vert, color: AppColors.textSecondary, size: 20),
        onSelected: (action) {
          if (action == 'edit') onEdit();
          if (action == 'delete') onDelete();
        },
        itemBuilder: (_) => const [
          PopupMenuItem(
              value: 'edit',
              child: ListTile(
                  leading: Icon(Icons.edit_outlined),
                  title: Text('Editar'),
                  contentPadding: EdgeInsets.zero)),
          PopupMenuItem(
              value: 'delete',
              child: ListTile(
                  leading: Icon(Icons.delete_outline, color: AppColors.expense),
                  title: Text('Eliminar', style: TextStyle(color: AppColors.expense)),
                  contentPadding: EdgeInsets.zero)),
        ],
      ),
    );
  }
}

/// Pequeño texto con icono y color, usado como subtítulo de tipo (Ingreso/Gasto).
class StatusBadgeText extends StatelessWidget {
  final String label;
  final Color color;
  const StatusBadgeText({super.key, required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 6,
          height: 6,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        ),
        const SizedBox(width: 6),
        Text(label, style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.w600)),
      ],
    );
  }
}
