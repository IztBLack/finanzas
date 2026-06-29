import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants.dart';
import '../../../shared/widgets/amount_chip.dart';
import '../../../shared/widgets/confirm_dialog.dart';
import '../../../shared/widgets/empty_state.dart';
import '../../../shared/widgets/list_item_card.dart';
import '../../../shared/widgets/loading_state.dart';
import '../providers/transactions_provider.dart';
import '../models/transaction.dart';

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
        tooltip: 'Nuevo movimiento',
        child: const Icon(Icons.add),
      ),
      body: state.when(
        loading: () => const SkeletonList(),
        error: (e, _) => ErrorState(
          message: e.toString(),
          onRetry: () => ref.read(transactionsProvider.notifier).refresh(),
        ),
        data: (txs) => RefreshIndicator(
          onRefresh: () => ref.read(transactionsProvider.notifier).refresh(),
          child: txs.isEmpty
              ? ListView(
                  children: [
                    SizedBox(
                      height: MediaQuery.of(context).size.height * 0.7,
                      child: EmptyState(
                        message: 'Sin movimientos aún',
                        subtitle: 'Registra tu primer ingreso o gasto',
                        icon: Icons.receipt_long_outlined,
                        actionLabel: 'Nuevo movimiento',
                        onAction: () async {
                          await context.push('/transactions/add');
                          ref.read(transactionsProvider.notifier).refresh();
                        },
                      ),
                    ),
                  ],
                )
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
                  itemCount: txs.length,
                  itemBuilder: (ctx, i) {
                    final tx = txs[i];
                    return _TransactionTile(
                      tx: tx,
                      onEdit: () async {
                        await context.push('/transactions/${tx.id}/edit');
                        ref.read(transactionsProvider.notifier).refresh();
                      },
                      onDelete: () async {
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
                      },
                    );
                  },
                ),
        ),
      ),
    );
  }
}

class _TransactionTile extends StatelessWidget {
  final AppTransaction tx;
  final VoidCallback onEdit;
  final VoidCallback onDelete;

  const _TransactionTile({
    required this.tx,
    required this.onEdit,
    required this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    final color = tx.isIncome ? AppColors.income : AppColors.expense;

    return ListItemCard(
      leading: CircleIconBadge(
        icon: tx.isIncome ? Icons.arrow_downward : Icons.arrow_upward,
        color: color,
      ),
      title: Text(
        tx.description.isNotEmpty ? tx.description : (tx.categoryName ?? '—'),
        style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
        maxLines: 1,
        overflow: TextOverflow.ellipsis,
      ),
      subtitle: Row(
        children: [
          Flexible(
            child: Text(
              '${tx.categoryName ?? '—'} · ${tx.accountName ?? ''}',
              style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          const Text(' · ', style: TextStyle(color: AppColors.textSecondary, fontSize: 12)),
          Text(tx.transactionDate,
              style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
        ],
      ),
      trailing: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          AmountChip(amount: tx.amount, isIncome: tx.isIncome),
          PopupMenuButton<String>(
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
        ],
      ),
    );
  }
}
