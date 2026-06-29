import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../core/constants.dart';
import '../../../shared/widgets/confirm_dialog.dart';
import '../../../shared/widgets/empty_state.dart';
import '../../../shared/widgets/list_item_card.dart';
import '../../../shared/widgets/loading_state.dart';
import '../providers/loans_provider.dart';
import '../models/loan.dart';

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
        tooltip: 'Nuevo préstamo',
        child: const Icon(Icons.add),
      ),
      body: state.when(
        loading: () => const SkeletonList(),
        error: (e, _) => ErrorState(
          message: e.toString(),
          onRetry: () => ref.read(loansProvider.notifier).refresh(),
        ),
        data: (loans) => RefreshIndicator(
          onRefresh: () => ref.read(loansProvider.notifier).refresh(),
          child: loans.isEmpty
              ? ListView(
                  children: [
                    SizedBox(
                      height: MediaQuery.of(context).size.height * 0.7,
                      child: EmptyState(
                        message: 'Sin préstamos registrados',
                        subtitle: 'Lleva el control de dinero que has prestado',
                        icon: Icons.handshake_outlined,
                        actionLabel: 'Nuevo préstamo',
                        onAction: () async {
                          await context.push('/loans/add');
                          ref.read(loansProvider.notifier).refresh();
                        },
                      ),
                    ),
                  ],
                )
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
                  itemCount: loans.length,
                  itemBuilder: (ctx, i) {
                    final loan = loans[i];
                    return _LoanTile(
                      loan: loan,
                      currency: currency,
                      onTap: () => context.go('/loans/${loan.id}'),
                      onEdit: () async {
                        await context.push('/loans/${loan.id}/edit');
                        ref.read(loansProvider.notifier).refresh();
                      },
                      onDelete: () async {
                        final ok = await showConfirmDialog(
                          context,
                          title: 'Eliminar préstamo',
                          message:
                              '¿Eliminar el préstamo a ${loan.debtorName}?',
                        );
                        if (ok) {
                          await ref.read(loansProvider.notifier).delete(loan.id);
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

class _LoanTile extends StatelessWidget {
  final Loan loan;
  final NumberFormat currency;
  final VoidCallback onTap;
  final VoidCallback onEdit;
  final VoidCallback onDelete;

  const _LoanTile({
    required this.loan,
    required this.currency,
    required this.onTap,
    required this.onEdit,
    required this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    return ListItemCard(
      onTap: onTap,
      leading: const CircleIconBadge(
        icon: Icons.handshake_outlined,
        color: AppColors.expense,
      ),
      title: Text(loan.debtorName,
          style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
          maxLines: 1,
          overflow: TextOverflow.ellipsis),
      subtitle: Row(
        children: [
          Text(currency.format(loan.amount),
              style: const TextStyle(
                  color: AppColors.textPrimary,
                  fontSize: 12,
                  fontWeight: FontWeight.w600)),
          const Text(' · ', style: TextStyle(color: AppColors.textSecondary, fontSize: 12)),
          Text(loan.loanDate,
              style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
        ],
      ),
      trailing: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.chevron_right, color: AppColors.textSecondary),
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
