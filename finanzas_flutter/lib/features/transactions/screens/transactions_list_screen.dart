import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants.dart';
import '../../../shared/widgets/amount_chip.dart';
import '../../../shared/widgets/confirm_dialog.dart';
import '../../../shared/widgets/empty_state.dart';
import '../providers/transactions_provider.dart';

class TransactionsListScreen extends ConsumerStatefulWidget {
  const TransactionsListScreen({super.key});

  @override
  ConsumerState<TransactionsListScreen> createState() =>
      _TransactionsListScreenState();
}

class _TransactionsListScreenState
    extends ConsumerState<TransactionsListScreen> with WidgetsBindingObserver {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      ref.read(transactionsProvider.notifier).refresh();
    }
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(transactionsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Movimientos'),
        leading: Builder(builder: (ctx) => IconButton(
          icon: const Icon(Icons.menu),
          onPressed: () => Scaffold.of(ctx).openDrawer(),
        )),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          await context.push('/transactions/add');
          ref.read(transactionsProvider.notifier).refresh();
        },
        child: const Icon(Icons.add),
      ),
      body: state.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => ErrorState(
          message: e.toString(),
          onRetry: () => ref.read(transactionsProvider.notifier).refresh(),
        ),
        data: (txs) => RefreshIndicator(
          onRefresh: () => ref.read(transactionsProvider.notifier).refresh(),
          child: txs.isEmpty
              ? const EmptyState(
                  message: 'Sin movimientos aún',
                  icon: Icons.receipt_long_outlined)
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
                  itemCount: txs.length,
                  itemBuilder: (ctx, i) {
                    final tx = txs[i];
                    final color =
                        tx.isIncome ? AppColors.income : AppColors.expense;
                    return Card(
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: color.withOpacity(0.12),
                          child: Icon(
                            tx.isIncome
                                ? Icons.arrow_downward
                                : Icons.arrow_upward,
                            color: color,
                            size: 18,
                          ),
                        ),
                        title: Text(
                          tx.description.isNotEmpty
                              ? tx.description
                              : tx.categoryName ?? '—',
                          style: const TextStyle(fontWeight: FontWeight.w600),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        subtitle: Text(
                          '${tx.categoryName} · ${tx.transactionDate}',
                          style: const TextStyle(
                              color: AppColors.textSecondary, fontSize: 12),
                        ),
                        trailing: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            AmountChip(
                                amount: tx.amount, isIncome: tx.isIncome),
                            PopupMenuButton<String>(
                              onSelected: (action) async {
                                if (action == 'edit') {
                                  await context.push(
                                      '/transactions/${tx.id}/edit');
                                  ref
                                      .read(transactionsProvider.notifier)
                                      .refresh();
                                } else if (action == 'delete') {
                                  final ok = await showConfirmDialog(
                                    context,
                                    title: 'Eliminar movimiento',
                                    message: '¿Eliminar este movimiento?',
                                  );
                                  if (ok) {
                                    await ref
                                        .read(transactionsProvider.notifier)
                                        .delete(tx.id);
                                  }
                                }
                              },
                              itemBuilder: (_) => const [
                                PopupMenuItem(
                                    value: 'edit', child: Text('Editar')),
                                PopupMenuItem(
                                    value: 'delete',
                                    child: Text('Eliminar',
                                        style: TextStyle(
                                            color: AppColors.expense))),
                              ],
                            ),
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
