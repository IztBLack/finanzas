import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/constants.dart';
import '../../../features/accounts/providers/accounts_provider.dart';
import '../../../features/categories/providers/categories_provider.dart';
import '../../../shared/widgets/form_widgets.dart';
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

  bool get _isEditing => widget.transactionId != null;

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
      showAppSnackBar(context, 'Selecciona una cuenta', isError: true);
      return;
    }
    if (_categoryId == null) {
      showAppSnackBar(context, 'Selecciona una categoría', isError: true);
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
      if (mounted) showAppSnackBar(context, e.toString(), isError: true);
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
    final dateLabel  = DateFormat('d MMM yyyy', 'es_MX')
        .format(DateTime.tryParse(_date) ?? DateTime.now());

    return Scaffold(
      appBar: AppBar(
        title: Text(_isEditing ? 'Editar movimiento' : 'Nuevo movimiento'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 32),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Tipo
              const FormSectionLabel('Tipo de movimiento'),
              Row(children: [
                Expanded(child: FormToggleButton(
                    label: 'Gasto',
                    icon: Icons.arrow_upward,
                    selected: _type == 'expense',
                    color: AppColors.expense,
                    onTap: () => setState(() { _type = 'expense'; _categoryId = null; }))),
                const SizedBox(width: 10),
                Expanded(child: FormToggleButton(
                    label: 'Ingreso',
                    icon: Icons.arrow_downward,
                    selected: _type == 'income',
                    color: AppColors.income,
                    onTap: () => setState(() { _type = 'income'; _categoryId = null; }))),
              ]),
              const SizedBox(height: 20),

              // Monto
              TextFormField(
                controller: _amountCtrl,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w700),
                decoration: const InputDecoration(
                    labelText: 'Monto', prefixText: '\$ '),
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Requerido';
                  final n = double.tryParse(v);
                  if (n == null || n <= 0) return 'Monto inválido';
                  return null;
                },
              ),
              const SizedBox(height: 20),

              // Cuenta
              DropdownButtonFormField<int>(
                initialValue: _accountId,
                decoration: const InputDecoration(
                    labelText: 'Cuenta',
                    prefixIcon: Icon(Icons.account_balance_wallet_outlined)),
                items: accounts.map((a) => DropdownMenuItem(
                    value: a.id,
                    child: Text(a.name, overflow: TextOverflow.ellipsis))).toList(),
                onChanged: (v) => setState(() => _accountId = v),
                validator: (v) => v == null ? 'Selecciona una cuenta' : null,
              ),
              const SizedBox(height: 20),

              // Categoría
              if (filtCats.isEmpty)
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: AppColors.warning.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.warning.withOpacity(0.4)),
                  ),
                  child: Row(
                    children: const [
                      Icon(Icons.info_outline, color: AppColors.warning, size: 18),
                      SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'No tienes categorías de este tipo. Crea una desde el menú de Categorías.',
                          style: TextStyle(color: AppColors.warning, fontSize: 12.5),
                        ),
                      ),
                    ],
                  ),
                )
              else
                DropdownButtonFormField<int>(
                  initialValue: filtCats.any((c) => c.id == _categoryId)
                      ? _categoryId
                      : null,
                  decoration: const InputDecoration(
                      labelText: 'Categoría',
                      prefixIcon: Icon(Icons.label_outline)),
                  items: filtCats.map((c) => DropdownMenuItem(
                      value: c.id,
                      child: Text(c.name, overflow: TextOverflow.ellipsis))).toList(),
                  onChanged: (v) => setState(() => _categoryId = v),
                  validator: (v) => v == null ? 'Selecciona una categoría' : null,
                ),
              const SizedBox(height: 20),

              // Fecha
              GestureDetector(
                onTap: _pickDate,
                child: InputDecorator(
                  decoration: const InputDecoration(
                      labelText: 'Fecha',
                      prefixIcon: Icon(Icons.calendar_today_outlined),
                      suffixIcon: Icon(Icons.keyboard_arrow_down)),
                  child: Text(dateLabel,
                      style: const TextStyle(fontWeight: FontWeight.w500)),
                ),
              ),
              const SizedBox(height: 20),

              // Descripción
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
                label: _isEditing ? 'Actualizar' : 'Registrar movimiento',
              ),
            ],
          ),
        ),
      ),
    );
  }
}
