import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../features/accounts/providers/accounts_provider.dart';
import '../../../shared/widgets/form_widgets.dart';
import '../providers/loans_provider.dart';
import '../models/loan.dart';

class LoanFormScreen extends ConsumerStatefulWidget {
  final int? loanId;
  const LoanFormScreen({super.key, this.loanId});

  @override
  ConsumerState<LoanFormScreen> createState() => _LoanFormScreenState();
}

class _LoanFormScreenState extends ConsumerState<LoanFormScreen> {
  final _formKey    = GlobalKey<FormState>();
  final _debtorCtrl = TextEditingController();
  final _amountCtrl = TextEditingController();
  final _descCtrl   = TextEditingController();
  int?   _accountId;
  String _loanDate  = DateTime.now().toIso8601String().split('T').first;
  bool   _loading   = false;

  bool get _isEditing => widget.loanId != null;

  @override
  void initState() {
    super.initState();
    if (widget.loanId != null) {
      final loans = ref.read(loansProvider).valueOrNull ?? [];
      final loan = loans.cast<Loan?>()
          .firstWhere((l) => l?.id == widget.loanId, orElse: () => null);
      if (loan != null) {
        _debtorCtrl.text = loan.debtorName;
        _amountCtrl.text = loan.amount.toString();
        _descCtrl.text   = loan.description;
        _accountId       = loan.accountId;
        _loanDate        = loan.loanDate;
      }
    }
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.tryParse(_loanDate) ?? DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() => _loanDate = picked.toIso8601String().split('T').first);
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_accountId == null) {
      showAppSnackBar(context, 'Selecciona una cuenta', isError: true);
      return;
    }
    setState(() => _loading = true);
    final data = {
      'account_id':  _accountId,
      'debtor_name': _debtorCtrl.text.trim(),
      'amount':      double.tryParse(_amountCtrl.text) ?? 0,
      'loan_date':   _loanDate,
      'description': _descCtrl.text.trim(),
    };
    try {
      if (widget.loanId != null) {
        await ref.read(loansProvider.notifier).edit(widget.loanId!, data);
      } else {
        await ref.read(loansProvider.notifier).create(data);
      }
      if (mounted) Navigator.of(context).pop(true);
    } catch (e) {
      if (mounted) showAppSnackBar(context, e.toString(), isError: true);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  void dispose() {
    _debtorCtrl.dispose(); _amountCtrl.dispose(); _descCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final accounts = ref.watch(accountsProvider).valueOrNull ?? [];
    final dateLabel = DateFormat('d MMM yyyy', 'es_MX')
        .format(DateTime.tryParse(_loanDate) ?? DateTime.now());

    return Scaffold(
      appBar: AppBar(
        title: Text(_isEditing ? 'Editar préstamo' : 'Nuevo préstamo'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 32),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TextFormField(
                controller: _debtorCtrl,
                textInputAction: TextInputAction.next,
                decoration: const InputDecoration(
                    labelText: 'Nombre del deudor',
                    prefixIcon: Icon(Icons.person_outline)),
                validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 20),
              TextFormField(
                controller: _amountCtrl,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w700),
                decoration: const InputDecoration(labelText: 'Monto', prefixText: '\$ '),
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Requerido';
                  final n = double.tryParse(v);
                  if (n == null || n <= 0) return 'Monto inválido';
                  return null;
                },
              ),
              const SizedBox(height: 20),
              DropdownButtonFormField<int>(
                initialValue:
                    accounts.any((a) => a.id == _accountId) ? _accountId : null,
                decoration: const InputDecoration(
                    labelText: 'Cuenta de origen',
                    prefixIcon: Icon(Icons.account_balance_wallet_outlined)),
                items: accounts.map((a) => DropdownMenuItem(
                    value: a.id, child: Text(a.name))).toList(),
                onChanged: (v) => setState(() => _accountId = v),
                validator: (v) => v == null ? 'Selecciona una cuenta' : null,
              ),
              const SizedBox(height: 20),
              GestureDetector(
                onTap: _pickDate,
                child: InputDecorator(
                  decoration: const InputDecoration(
                      labelText: 'Fecha del préstamo',
                      prefixIcon: Icon(Icons.calendar_today_outlined),
                      suffixIcon: Icon(Icons.keyboard_arrow_down)),
                  child: Text(dateLabel,
                      style: const TextStyle(fontWeight: FontWeight.w500)),
                ),
              ),
              const SizedBox(height: 20),
              TextFormField(
                controller: _descCtrl,
                textInputAction: TextInputAction.done,
                decoration: const InputDecoration(
                    labelText: 'Descripción (opcional)',
                    prefixIcon: Icon(Icons.notes_outlined)),
              ),
              const SizedBox(height: 36),
              SubmitButton(
                loading: _loading,
                onPressed: _submit,
                icon: _isEditing ? Icons.save_outlined : Icons.add,
                label: _isEditing ? 'Actualizar' : 'Registrar préstamo',
              ),
            ],
          ),
        ),
      ),
    );
  }
}
