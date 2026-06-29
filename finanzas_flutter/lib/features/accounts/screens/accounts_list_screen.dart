import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../core/constants.dart';
import '../../../shared/widgets/confirm_dialog.dart';
import '../../../shared/widgets/empty_state.dart';
import '../../../shared/widgets/list_item_card.dart';
import '../../../shared/widgets/loading_state.dart';
import '../providers/accounts_provider.dart';
import '../models/account.dart';

class AccountsListScreen extends ConsumerWidget {
  const AccountsListScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(accountsProvider);
    final currency = NumberFormat.currency(locale: 'es_MX', symbol: '\$');

    return Scaffold(
      appBar: AppBar(
        title: const Text('Cuentas'),
        leading: Builder(builder: (ctx) => IconButton(
          icon: const Icon(Icons.menu),
          onPressed: () => Scaffold.of(ctx).openDrawer(),
        )),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          await context.push('/accounts/add');
          ref.read(accountsProvider.notifier).refresh();
        },
        tooltip: 'Nueva cuenta',
        child: const Icon(Icons.add),
      ),
      body: state.when(
        loading: () => const SkeletonList(),
        error: (e, _) => ErrorState(
          message: e.toString(),
          onRetry: () => ref.read(accountsProvider.notifier).refresh(),
        ),
        data: (accounts) => RefreshIndicator(
          onRefresh: () => ref.read(accountsProvider.notifier).refresh(),
          child: accounts.isEmpty
              ? ListView(
                  children: [
                    SizedBox(
                      height: MediaQuery.of(context).size.height * 0.7,
                      child: EmptyState(
                        message: 'No tienes cuentas aún',
                        subtitle: 'Agrega tu primera cuenta de débito o crédito',
                        icon: Icons.account_balance_wallet_outlined,
                        actionLabel: 'Nueva cuenta',
                        onAction: () async {
                          await context.push('/accounts/add');
                          ref.read(accountsProvider.notifier).refresh();
                        },
                      ),
                    ),
                  ],
                )
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
                  itemCount: accounts.length,
                  itemBuilder: (ctx, i) {
                    final acc = accounts[i];
                    return _AccountTile(
                      account: acc,
                      currency: currency,
                      onEdit: () async {
                        await context.push('/accounts/${acc.id}/edit');
                        ref.read(accountsProvider.notifier).refresh();
                      },
                      onDelete: () async {
                        final ok = await showConfirmDialog(
                          context,
                          title: 'Eliminar cuenta',
                          message:
                              '¿Eliminar "${acc.name}"? Esta acción no se puede deshacer.',
                        );
                        if (ok) {
                          await ref
                              .read(accountsProvider.notifier)
                              .delete(acc.id);
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

class _AccountTile extends StatelessWidget {
  final Account account;
  final NumberFormat currency;
  final VoidCallback onEdit;
  final VoidCallback onDelete;

  const _AccountTile({
    required this.account,
    required this.currency,
    required this.onEdit,
    required this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    final color = account.isCredit ? AppColors.expense : AppColors.primary;

    return ListItemCard(
      leading: CircleIconBadge(
        icon: account.isCredit ? Icons.credit_card : Icons.account_balance_wallet,
        color: color,
      ),
      title: Text(account.name,
          style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
          maxLines: 1,
          overflow: TextOverflow.ellipsis),
      subtitle: Row(
        children: [
          Icon(
            account.isCredit ? Icons.credit_card_outlined : Icons.payments_outlined,
            size: 12,
            color: AppColors.textSecondary,
          ),
          const SizedBox(width: 4),
          Text(
            account.isCredit ? 'Tarjeta de crédito' : 'Débito / Efectivo',
            style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
          ),
          if (account.isCredit && account.creditLimit != null) ...[
            const Text(' · ', style: TextStyle(color: AppColors.textSecondary, fontSize: 12)),
            Text(
              'Límite ${currency.format(account.creditLimit)}',
              style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
            ),
          ],
        ],
      ),
      trailing: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                currency.format(account.initialBalance),
                style: const TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 14,
                    color: AppColors.textPrimary),
              ),
              const Text('saldo inicial',
                  style: TextStyle(color: AppColors.textSecondary, fontSize: 10)),
            ],
          ),
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
