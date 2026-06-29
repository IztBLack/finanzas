import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/constants.dart';
import '../../../features/accounts/providers/accounts_provider.dart';
import '../../../shared/widgets/confirm_dialog.dart';
import '../../../shared/widgets/empty_state.dart';
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
          body: const Center(child: CircularProgressIndicator()));
    }

    final totalPaid = paymentsAsync.valueOrNull
            ?.fold<double>(0, (sum, p) => sum + p.amount) ??
        0;
    final pending = (loan.amount as double) - totalPaid;

    return Scaffold(
      appBar: AppBar(title: Text(loan.debtorName as String)),
      floatingActionButton: FloatingActionButton.extended(
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
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.card,
                borderRadius: BorderRadius.circular(18),
                border: const Border.fromBorderSide(
                    BorderSide(color: AppColors.cardBorder)),
              ),
              child: Column(
                children: [
                  _InfoRow('Monto prestado',
                      currency.format(loan.amount as double)),
                  _InfoRow('Total pagado', currency.format(totalPaid),
                      valueColor: AppColors.income),
                  _InfoRow('Pendiente', currency.format(pending),
                      valueColor: pending > 0
                          ? AppColors.expense
                          : AppColors.income),
                  _InfoRow('Fecha', loan.loanDate as String),
                  if ((loan.description as String).isNotEmpty)
                    _InfoRow('Nota', loan.description as String),
                ],
              ),
            ),

            const SizedBox(height: 20),
            const Text('Pagos registrados',
                style: TextStyle(
                    fontWeight: FontWeight.w700, fontSize: 16)),
            const SizedBox(height: 8),

            // ── Pagos ────────────────────────────────────────────────
            paymentsAsync.when(
              loading: () =>
                  const Center(child: CircularProgressIndicator()),
              error: (e, _) =>
                  ErrorState(message: e.toString()),
              data: (payments) => payments.isEmpty
                  ? const EmptyState(
                      message: 'Sin pagos aún',
                      icon: Icons.payments_outlined)
                  : Column(
                      children: payments.map((p) => Card(
                        child: ListTile(
                          leading: const CircleAvatar(
                            backgroundColor: Color(0x2600BFA5),
                            child: Icon(Icons.arrow_downward,
                                color: AppColors.income, size: 18),
                          ),
                          title: Text(currency.format(p.amount),
                              style: const TextStyle(
                                  fontWeight: FontWeight.w700,
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
                                ref.invalidate(loanPaymentsProvider(widget.loanId));
                              }
                            },
                          ),
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
              const Text('Registrar pago',
                  style: TextStyle(
                      fontWeight: FontWeight.w700, fontSize: 18)),
              const SizedBox(height: 16),
              TextField(
                controller: amountCtrl,
                keyboardType:
                    const TextInputType.numberWithOptions(decimal: true),
                decoration: const InputDecoration(
                    labelText: 'Monto', prefixText: '\$ '),
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<int>(
                value: accountId,
                decoration: const InputDecoration(labelText: 'Cuenta'),
                items: accounts.map((a) => DropdownMenuItem(
                    value: a.id, child: Text(a.name))).toList(),
                onChanged: (v) => setSheetState(() => accountId = v),
              ),
              const SizedBox(height: 12),
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
                      suffixIcon: Icon(Icons.calendar_today_outlined)),
                  child: Text(date),
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: () async {
                    final amount = double.tryParse(amountCtrl.text);
                    if (amount == null || amount <= 0 || accountId == null) {
                      return;
                    }
                    await _repo.addPayment(loanId, {
                      'account_id':   accountId,
                      'amount':       amount,
                      'payment_date': date,
                    });
                    ref.invalidate(loanPaymentsProvider(loanId));
                    if (sheetCtx.mounted) Navigator.of(sheetCtx).pop();
                  },
                  child: const Text('Registrar pago'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final String label;
  final String value;
  final Color? valueColor;
  const _InfoRow(this.label, this.value, {this.valueColor});

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
          Text(value,
              style: TextStyle(
                  fontWeight: FontWeight.w700,
                  fontSize: 14,
                  color: valueColor ?? AppColors.textPrimary)),
        ],
      ),
    );
  }
}
