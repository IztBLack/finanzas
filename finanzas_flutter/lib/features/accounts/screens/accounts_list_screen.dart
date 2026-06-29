import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../core/constants.dart';
import '../../../shared/widgets/confirm_dialog.dart';
import '../../../shared/widgets/empty_state.dart';
import '../providers/accounts_provider.dart';

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
        child: const Icon(Icons.add),
      ),
      body: state.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => ErrorState(
          message: e.toString(),
          onRetry: () => ref.read(accountsProvider.notifier).refresh(),
        ),
        data: (accounts) => RefreshIndicator(
          onRefresh: () => ref.read(accountsProvider.notifier).refresh(),
          child: accounts.isEmpty
              ? const EmptyState(
                  message: 'No tienes cuentas aún',
                  icon: Icons.account_balance_wallet_outlined)
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
                  itemCount: accounts.length,
                  itemBuilder: (ctx, i) {
                    final acc = accounts[i];
                    return Card(
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: acc.isCredit
                              ? AppColors.expense.withOpacity(0.15)
                              : AppColors.primary.withOpacity(0.15),
                          child: Icon(
                            acc.isCredit
                                ? Icons.credit_card
                                : Icons.account_balance_wallet,
                            color: acc.isCredit
                                ? AppColors.expense
                                : AppColors.primary,
                            size: 20,
                          ),
                        ),
                        title: Text(acc.name,
                            style: const TextStyle(fontWeight: FontWeight.w600)),
                        subtitle: Text(
                          acc.isCredit ? 'Tarjeta de crédito' : 'Débito / Efectivo',
                          style: const TextStyle(
                              color: AppColors.textSecondary, fontSize: 12),
                        ),
                        trailing: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                Text(
                                  currency.format(acc.initialBalance),
                                  style: const TextStyle(
                                      fontWeight: FontWeight.w700,
                                      fontSize: 14,
                                      color: AppColors.textPrimary),
                                ),
                                const Text('saldo inicial',
                                    style: TextStyle(
                                        color: AppColors.textSecondary,
                                        fontSize: 10)),
                              ],
                            ),
                            const SizedBox(width: 8),
                            PopupMenuButton<String>(
                              onSelected: (action) async {
                                if (action == 'edit') {
                                  await context.push('/accounts/${acc.id}/edit');
                                  ref.read(accountsProvider.notifier).refresh();
                                } else if (action == 'delete') {
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
                                }
                              },
                              itemBuilder: (_) => const [
                                PopupMenuItem(value: 'edit',
                                    child: Text('Editar')),
                                PopupMenuItem(value: 'delete',
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
