import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../core/constants.dart';
import '../../../shared/widgets/confirm_dialog.dart';
import '../../../shared/widgets/empty_state.dart';
import '../providers/subscriptions_provider.dart';

class SubscriptionsListScreen extends ConsumerWidget {
  const SubscriptionsListScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state    = ref.watch(subscriptionsProvider);
    final currency = NumberFormat.currency(locale: 'es_MX', symbol: '\$');

    return Scaffold(
      appBar: AppBar(title: const Text('Suscripciones')),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          await context.push('/subscriptions/add');
          ref.read(subscriptionsProvider.notifier).refresh();
        },
        child: const Icon(Icons.add),
      ),
      body: state.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => ErrorState(
          message: e.toString(),
          onRetry: () => ref.read(subscriptionsProvider.notifier).refresh(),
        ),
        data: (subs) => RefreshIndicator(
          onRefresh: () => ref.read(subscriptionsProvider.notifier).refresh(),
          child: subs.isEmpty
              ? const EmptyState(
                  message: 'Sin suscripciones registradas',
                  icon: Icons.subscriptions_outlined)
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
                  itemCount: subs.length,
                  itemBuilder: (ctx, i) {
                    final sub = subs[i];
                    return Card(
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: sub.isActive
                              ? AppColors.expense.withOpacity(0.12)
                              : Colors.grey.withOpacity(0.12),
                          child: Icon(Icons.subscriptions_outlined,
                              color: sub.isActive
                                  ? AppColors.expense
                                  : Colors.grey,
                              size: 20),
                        ),
                        title: Row(
                          children: [
                            Text(sub.name,
                                style: const TextStyle(
                                    fontWeight: FontWeight.w600)),
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: sub.isActive
                                    ? AppColors.income.withOpacity(0.15)
                                    : Colors.grey.withOpacity(0.15),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                sub.isActive ? 'Activa' : 'Pausada',
                                style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w600,
                                    color: sub.isActive
                                        ? AppColors.income
                                        : Colors.grey),
                              ),
                            ),
                          ],
                        ),
                        subtitle: Text(
                          '${currency.format(sub.amount)} · Día ${sub.billingDay} · ${sub.isMonthly ? 'Mensual' : 'Anual'}',
                          style: const TextStyle(
                              color: AppColors.textSecondary, fontSize: 12),
                        ),
                        trailing: PopupMenuButton<String>(
                          onSelected: (action) async {
                            if (action == 'edit') {
                              await context.push('/subscriptions/${sub.id}/edit');
                              ref.read(subscriptionsProvider.notifier).refresh();
                            } else if (action == 'delete') {
                              final ok = await showConfirmDialog(
                                context,
                                title: 'Eliminar suscripción',
                                message: '¿Eliminar "${sub.name}"?',
                              );
                              if (ok) {
                                await ref
                                    .read(subscriptionsProvider.notifier)
                                    .delete(sub.id);
                              }
                            }
                          },
                          itemBuilder: (_) => const [
                            PopupMenuItem(
                                value: 'edit', child: Text('Editar')),
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
