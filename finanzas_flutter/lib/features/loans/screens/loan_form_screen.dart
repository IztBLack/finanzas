import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../features/accounts/providers/accounts_provider.dart';
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
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Selecciona una cuenta')));
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
      if (mounted) ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(e.toString())));
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

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.loanId != null ? 'Editar préstamo' : 'Nuevo préstamo'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              TextFormField(
                controller: _debtorCtrl,
                decoration: const InputDecoration(labelText: 'Nombre del deudor'),
                validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _amountCtrl,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                decoration: const InputDecoration(labelText: 'Monto', prefixText: '\$ '),
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Requerido';
                  final n = double.tryParse(v);
                  if (n == null || n <= 0) return 'Monto inválido';
                  return null;
                },
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<int>(
                value: _accountId,
                decoration: const InputDecoration(labelText: 'Cuenta'),
                items: accounts.map((a) => DropdownMenuItem(
                    value: a.id, child: Text(a.name))).toList(),
                onChanged: (v) => setState(() => _accountId = v),
                validator: (v) => v == null ? 'Selecciona una cuenta' : null,
              ),
              const SizedBox(height: 16),
              GestureDetector(
                onTap: _pickDate,
                child: InputDecorator(
                  decoration: const InputDecoration(
                      labelText: 'Fecha',
                      suffixIcon: Icon(Icons.calendar_today_outlined)),
                  child: Text(_loanDate),
                ),
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _descCtrl,
                decoration: const InputDecoration(labelText: 'Descripción (opcional)'),
              ),
              const SizedBox(height: 32),
              ElevatedButton(
                onPressed: _loading ? null : _submit,
                child: _loading
                    ? const SizedBox(width: 22, height: 22,
                        child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white))
                    : Text(widget.loanId != null ? 'Actualizar' : 'Registrar préstamo'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
