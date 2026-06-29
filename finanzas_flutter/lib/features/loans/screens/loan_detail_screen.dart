import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/constants.dart';
import '../../../features/accounts/providers/accounts_provider.dart';
import '../../../shared/widgets/confirm_dialog.dart';
import '../../../shared/widgets/empty_state.dart';
import '../../../shared/widgets/form_widgets.dart';
import '../../../shared/widgets/glass_card.dart';
import '../../../shared/widgets/list_item_card.dart';
import '../../../shared/widgets/loading_state.dart';
import '../data/loans_repository.dart';
import '../providers/loans_provider.dart';

class LoanDetailScreen extends ConsumerStatefulWidget {
  final int loanId;
  const LoanDetailScreen({super.key, required this.loanId});

  @override
  ConsumerState<LoanDetailScreen> createState() => _LoanDetailScreenState();
}

class _LoanDetailScreenState extends ConsumerState<LoanDetailScreen> {
  final _repo = LoansRepository();

  @override
  Widget build(BuildContext context) {
    final loansAsync   = ref.watch(loansProvider);
    final paymentsAsync = ref.watch(loanPaymentsProvider(widget.loanId));
    final currency      = NumberFormat.currency(locale: 'es_MX', symbol: '\$');

    final loan = loansAsync.valueOrNull
        ?.where((l) => l.id == widget.loanId)
        .cast<dynamic>()
        .firstOrNull;

    if (loan == null) {
      return Scaffold(
          appBar: AppBar(title: const Text('Préstamo')),
          body: const LoadingState());
    }

    final totalAmount = loan.amount as double;
    final totalPaid = paymentsAsync.valueOrNull
            ?.fold<double>(0, (sum, p) => sum + p.amount) ??
        0;
    final pending = totalAmount - totalPaid;
    final progress = totalAmount > 0 ? (totalPaid / totalAmount).clamp(0.0, 1.0) : 0.0;
    final isPaidOff = pending <= 0;

    return Scaffold(
      appBar: AppBar(title: Text(loan.debtorName as String)),
      floatingActionButton: isPaidOff
          ? null
          : FloatingActionButton.extended(
              onPressed: () => _addPayment(context, loan.id as int),
              icon: const Icon(Icons.add),
              label: const Text('Registrar pago'),
            ),
      body: RefreshIndicator(
        onRefresh: () async =>
            ref.invalidate(loanPaymentsProvider(widget.loanId)),
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
          children: [
            // ── Resumen ─────────────────────────────────────────────
            GlassCard(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Monto prestado',
                          style: TextStyle(
                              color: AppColors.textSecondary, fontSize: 13)),
                      if (isPaidOff)
                        const StatusBadge(label: 'PAGADO', color: AppColors.income),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    currency.format(totalAmount),
                    style: const TextStyle(
                        fontSize: 30,
                        fontWeight: FontWeight.w800,
                        color: AppColors.textPrimary,
                        letterSpacing: -1),
                  ),
                  const SizedBox(height: 16),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: LinearProgressIndicator(
                      value: progress,
                      minHeight: 8,
                      backgroundColor: AppColors.background,
                      color: isPaidOff ? AppColors.income : AppColors.primary,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: _StatBlock(
                          label: 'Pagado',
                          value: currency.format(totalPaid),
                          color: AppColors.income,
                        ),
                      ),
                      Container(width: 1, height: 32, color: AppColors.cardBorder),
                      Expanded(
                        child: _StatBlock(
                          label: 'Pendiente',
                          value: currency.format(pending < 0 ? 0 : pending),
                          color: pending > 0 ? AppColors.expense : AppColors.income,
                        ),
                      ),
                    ],
                  ),
                  const Divider(height: 28),
                  _InfoRow('Fecha del préstamo', loan.loanDate as String),
                  if ((loan.description as String).isNotEmpty)
                    _InfoRow('Nota', loan.description as String),
                ],
              ),
            ),

            const SizedBox(height: 20),
            const SectionHeader(title: 'Pagos registrados'),

            // ── Pagos ────────────────────────────────────────────────
            paymentsAsync.when(
              loading: () => const Padding(
                padding: EdgeInsets.symmetric(vertical: 24),
                child: LoadingState(),
              ),
              error: (e, _) =>
                  ErrorState(message: e.toString()),
              data: (payments) => payments.isEmpty
                  ? const Padding(
                      padding: EdgeInsets.symmetric(vertical: 12),
                      child: EmptyState(
                          message: 'Sin pagos aún',
                          icon: Icons.payments_outlined),
                    )
                  : Column(
                      children: payments.map((p) => ListItemCard(
                        leading: const CircleIconBadge(
                          icon: Icons.arrow_downward,
                          color: AppColors.income,
                        ),
                        title: Text(currency.format(p.amount),
                            style: const TextStyle(
                                fontWeight: FontWeight.w700,
                                fontSize: 15,
                                color: AppColors.income)),
                        subtitle: Text(
                          '${p.paymentDate} · ${p.accountName ?? ''}',
                          style: const TextStyle(
                              color: AppColors.textSecondary,
                              fontSize: 12),
                        ),
                        trailing: IconButton(
                          icon: const Icon(Icons.delete_outline,
                              color: AppColors.expense),
                          onPressed: () async {
                            final ok = await showConfirmDialog(
                              context,
                              title: 'Eliminar pago',
                              message: '¿Eliminar este pago?',
                            );
                            if (ok) {
                              await _repo.deletePayment(p.id);
                              if (context.mounted) {
                                ref.invalidate(loanPaymentsProvider(widget.loanId));
                              }
                            }
                          },
                        ),
                      )).toList(),
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _addPayment(BuildContext ctx, int loanId) async {
    final accounts = ref.read(accountsProvider).valueOrNull ?? [];
    int? accountId;
    final amountCtrl = TextEditingController();
    String date = DateTime.now().toIso8601String().split('T').first;
    String? errorText;
    bool submitting = false;

    await showModalBottomSheet(
      context: ctx,
      isScrollControlled: true,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (sheetCtx) => StatefulBuilder(
        builder: (sheetCtx, setSheetState) => Padding(
          padding: EdgeInsets.fromLTRB(
              20, 20, 20, MediaQuery.of(sheetCtx).viewInsets.bottom + 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 36,
                  height: 4,
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(
                    color: AppColors.cardBorder,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const Text('Registrar pago',
                  style: TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 18,
                      color: AppColors.textPrimary)),
              const SizedBox(height: 18),
              TextField(
                controller: amountCtrl,
                autofocus: true,
                keyboardType:
                    const TextInputType.numberWithOptions(decimal: true),
                decoration: InputDecoration(
                    labelText: 'Monto',
                    prefixIcon: const Icon(Icons.payments_outlined),
                    prefixText: '\$ ',
                    errorText: errorText),
              ),
              const SizedBox(height: 14),
              DropdownButtonFormField<int>(
                initialValue: accountId,
                decoration: const InputDecoration(
                    labelText: 'Cuenta',
                    prefixIcon: Icon(Icons.account_balance_wallet_outlined)),
                items: accounts.map((a) => DropdownMenuItem(
                    value: a.id, child: Text(a.name))).toList(),
                onChanged: (v) => setSheetState(() => accountId = v),
              ),
              const SizedBox(height: 14),
              GestureDetector(
                onTap: () async {
                  final picked = await showDatePicker(
                    context: sheetCtx,
                    initialDate: DateTime.now(),
                    firstDate: DateTime(2020),
                    lastDate: DateTime.now(),
                  );
                  if (picked != null) {
                    setSheetState(() =>
                        date = picked.toIso8601String().split('T').first);
                  }
                },
                child: InputDecorator(
                  decoration: const InputDecoration(
                      labelText: 'Fecha',
                      prefixIcon: Icon(Icons.calendar_today_outlined),
                      suffixIcon: Icon(Icons.keyboard_arrow_down)),
                  child: Text(date),
                ),
              ),
              const SizedBox(height: 22),
              SubmitButton(
                loading: submitting,
                label: 'Registrar pago',
                icon: Icons.check,
                onPressed: () async {
                  final amount = double.tryParse(amountCtrl.text);
                  if (amount == null || amount <= 0) {
                    setSheetState(() => errorText = 'Monto inválido');
                    return;
                  }
                  if (accountId == null) {
                    showAppSnackBar(sheetCtx, 'Selecciona una cuenta', isError: true);
                    return;
                  }
                  setSheetState(() { submitting = true; errorText = null; });
                  try {
                    await _repo.addPayment(loanId, {
                      'account_id':   accountId,
                      'amount':       amount,
                      'payment_date': date,
                    });
                    ref.invalidate(loanPaymentsProvider(loanId));
                    if (sheetCtx.mounted) Navigator.of(sheetCtx).pop();
                  } catch (e) {
                    setSheetState(() => submitting = false);
                    if (sheetCtx.mounted) {
                      showAppSnackBar(sheetCtx, e.toString(), isError: true);
                    }
                  }
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _StatBlock extends StatelessWidget {
  final String label;
  final String value;
  final Color color;
  const _StatBlock({required this.label, required this.value, required this.color});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
        const SizedBox(height: 4),
        Text(value,
            style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: color)),
      ],
    );
  }
}

class _InfoRow extends StatelessWidget {
  final String label;
  final String value;
  const _InfoRow(this.label, this.value);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label,
              style: const TextStyle(
                  color: AppColors.textSecondary, fontSize: 13)),
          Flexible(
            child: Text(value,
                textAlign: TextAlign.right,
                style: const TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 14,
                    color: AppColors.textPrimary)),
          ),
        ],
      ),
    );
  }
}
