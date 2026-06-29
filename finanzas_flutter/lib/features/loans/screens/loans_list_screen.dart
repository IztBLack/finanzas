import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../core/constants.dart';
import '../../../shared/widgets/confirm_dialog.dart';
import '../../../shared/widgets/empty_state.dart';
import '../providers/loans_provider.dart';

class LoansListScreen extends ConsumerWidget {
  const LoansListScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state    = ref.watch(loansProvider);
    final currency = NumberFormat.currency(locale: 'es_MX', symbol: '\$');

    return Scaffold(
      appBar: AppBar(title: const Text('Préstamos')),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          await context.push('/loans/add');
          ref.read(loansProvider.notifier).refresh();
        },
        child: const Icon(Icons.add),
      ),
      body: state.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => ErrorState(
          message: e.toString(),
          onRetry: () => ref.read(loansProvider.notifier).refresh(),
        ),
        data: (loans) => RefreshIndicator(
          onRefresh: () => ref.read(loansProvider.notifier).refresh(),
          child: loans.isEmpty
              ? const EmptyState(
                  message: 'Sin préstamos registrados',
                  icon: Icons.handshake_outlined)
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
                  itemCount: loans.length,
                  itemBuilder: (ctx, i) {
                    final loan = loans[i];
                    return Card(
                      child: ListTile(
                        leading: const CircleAvatar(
                          backgroundColor: Color(0x26EF5350),
                          child: Icon(Icons.handshake_outlined,
                              color: AppColors.expense, size: 20),
                        ),
                        title: Text(loan.debtorName,
                            style:
                                const TextStyle(fontWeight: FontWeight.w700)),
                        subtitle: Text(
                          '${currency.format(loan.amount)} · ${loan.loanDate}',
                          style: const TextStyle(
                              color: AppColors.textSecondary, fontSize: 12),
                        ),
                        trailing: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            TextButton(
                              onPressed: () =>
                                  context.go('/loans/${loan.id}'),
                              child: const Text('Ver'),
                            ),
                            PopupMenuButton<String>(
                              onSelected: (action) async {
                                if (action == 'edit') {
                                  await context.push('/loans/${loan.id}/edit');
                                  ref.read(loansProvider.notifier).refresh();
                                } else if (action == 'delete') {
                                  final ok = await showConfirmDialog(
                                    context,
                                    title: 'Eliminar préstamo',
                                    message:
                                        '¿Eliminar el préstamo a ${loan.debtorName}?',
                                  );
                                  if (ok) {
                                    await ref
                                        .read(loansProvider.notifier)
                                        .delete(loan.id);
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
