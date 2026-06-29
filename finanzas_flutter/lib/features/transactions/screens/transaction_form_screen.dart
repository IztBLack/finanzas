import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../features/accounts/providers/accounts_provider.dart';
import '../../../features/categories/providers/categories_provider.dart';
import '../providers/transactions_provider.dart';
import '../models/transaction.dart';

class TransactionFormScreen extends ConsumerStatefulWidget {
  final int? transactionId;
  const TransactionFormScreen({super.key, this.transactionId});

  @override
  ConsumerState<TransactionFormScreen> createState() =>
      _TransactionFormScreenState();
}

class _TransactionFormScreenState
    extends ConsumerState<TransactionFormScreen> {
  final _formKey    = GlobalKey<FormState>();
  final _descCtrl   = TextEditingController();
  final _amountCtrl = TextEditingController();
  String  _type     = 'expense';
  int?    _accountId;
  int?    _categoryId;
  String  _date     = DateTime.now().toIso8601String().split('T').first;
  bool    _loading  = false;

  @override
  void initState() {
    super.initState();
    if (widget.transactionId != null) _loadTx();
  }

  void _loadTx() {
    final txs = ref.read(transactionsProvider).valueOrNull ?? [];
    final tx = txs.cast<AppTransaction?>().firstWhere(
        (t) => t?.id == widget.transactionId,
        orElse: () => null);
    if (tx != null) {
      _descCtrl.text   = tx.description;
      _amountCtrl.text = tx.amount.toString();
      _type            = tx.type;
      _accountId       = tx.accountId;
      _categoryId      = tx.categoryId;
      _date            = tx.transactionDate;
    }
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.tryParse(_date) ?? DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() => _date = picked.toIso8601String().split('T').first);
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_accountId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Selecciona una cuenta')));
      return;
    }
    if (_categoryId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Selecciona una categoría')));
      return;
    }
    setState(() => _loading = true);

    final data = {
      'account_id':       _accountId,
      'category_id':      _categoryId,
      'amount':           double.tryParse(_amountCtrl.text) ?? 0,
      'type':             _type,
      'description':      _descCtrl.text.trim(),
      'transaction_date': _date,
    };

    try {
      if (widget.transactionId != null) {
        await ref.read(transactionsProvider.notifier).edit(widget.transactionId!, data);
      } else {
        await ref.read(transactionsProvider.notifier).create(data);
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
    _descCtrl.dispose(); _amountCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final accounts   = ref.watch(accountsProvider).valueOrNull ?? [];
    final categories = ref.watch(categoriesProvider).valueOrNull ?? [];
    final filtCats   = categories.where((c) => c.type == _type).toList();

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.transactionId != null
            ? 'Editar movimiento'
            : 'Nuevo movimiento'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Tipo
              const Text('Tipo', style: TextStyle(fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              Row(children: [
                Expanded(child: _TypeBtn(
                    label: 'Gasto', selected: _type == 'expense',
                    color: const Color(0xFFEF5350),
                    onTap: () => setState(() { _type = 'expense'; _categoryId = null; }))),
                const SizedBox(width: 10),
                Expanded(child: _TypeBtn(
                    label: 'Ingreso', selected: _type == 'income',
                    color: const Color(0xFF00BFA5),
                    onTap: () => setState(() { _type = 'income'; _categoryId = null; }))),
              ]),
              const SizedBox(height: 16),

              // Monto
              TextFormField(
                controller: _amountCtrl,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                decoration: const InputDecoration(
                    labelText: 'Monto', prefixText: '\$ '),
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Requerido';
                  final n = double.tryParse(v);
                  if (n == null || n <= 0) return 'Monto inválido';
                  return null;
                },
              ),
              const SizedBox(height: 16),

              // Cuenta
              DropdownButtonFormField<int>(
                value: _accountId,
                decoration: const InputDecoration(labelText: 'Cuenta'),
                items: accounts.map((a) => DropdownMenuItem(
                    value: a.id,
                    child: Text(a.name, overflow: TextOverflow.ellipsis))).toList(),
                onChanged: (v) => setState(() => _accountId = v),
                validator: (v) => v == null ? 'Selecciona una cuenta' : null,
              ),
              const SizedBox(height: 16),

              // Categoría
              DropdownButtonFormField<int>(
                value: filtCats.any((c) => c.id == _categoryId)
                    ? _categoryId
                    : null,
                decoration: const InputDecoration(labelText: 'Categoría'),
                items: filtCats.map((c) => DropdownMenuItem(
                    value: c.id,
                    child: Text(c.name, overflow: TextOverflow.ellipsis))).toList(),
                onChanged: (v) => setState(() => _categoryId = v),
                validator: (v) => v == null ? 'Selecciona una categoría' : null,
              ),
              const SizedBox(height: 16),

              // Fecha
              GestureDetector(
                onTap: _pickDate,
                child: InputDecorator(
                  decoration: const InputDecoration(
                      labelText: 'Fecha',
                      suffixIcon: Icon(Icons.calendar_today_outlined)),
                  child: Text(_date),
                ),
              ),
              const SizedBox(height: 16),

              // Descripción
              TextFormField(
                controller: _descCtrl,
                decoration: const InputDecoration(labelText: 'Descripción (opcional)'),
              ),
              const SizedBox(height: 32),

              ElevatedButton(
                onPressed: _loading ? null : _submit,
                child: _loading
                    ? const SizedBox(width: 22, height: 22,
                        child: CircularProgressIndicator(
                            strokeWidth: 2.5, color: Colors.white))
                    : Text(widget.transactionId != null
                        ? 'Actualizar'
                        : 'Registrar movimiento'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _TypeBtn extends StatelessWidget {
  final String label;
  final bool selected;
  final Color color;
  final VoidCallback onTap;
  const _TypeBtn(
      {required this.label,
      required this.selected,
      required this.color,
      required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: selected ? color.withOpacity(0.18) : const Color(0xFF1C2132),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
              color: selected ? color : const Color(0xFF262D42),
              width: selected ? 2 : 1),
        ),
        child: Text(label,
            textAlign: TextAlign.center,
            style: TextStyle(
                fontWeight: FontWeight.w600,
                color: selected ? color : const Color(0xFF8A93A8))),
      ),
    );
  }
}
