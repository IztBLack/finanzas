import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants.dart';
import '../../../shared/widgets/form_widgets.dart';
import '../providers/accounts_provider.dart';
import '../models/account.dart';

class AccountFormScreen extends ConsumerStatefulWidget {
  final int? accountId;
  const AccountFormScreen({super.key, this.accountId});

  @override
  ConsumerState<AccountFormScreen> createState() => _AccountFormScreenState();
}

class _AccountFormScreenState extends ConsumerState<AccountFormScreen> {
  final _formKey     = GlobalKey<FormState>();
  final _nameCtrl    = TextEditingController();
  final _balanceCtrl = TextEditingController(text: '0');
  final _creditCtrl  = TextEditingController();
  String _type       = 'debit';
  bool   _loading    = false;
  Account? _editing;

  bool get _isEditing => widget.accountId != null;

  @override
  void initState() {
    super.initState();
    if (widget.accountId != null) _loadAccount();
  }

  void _loadAccount() {
    final accounts = ref.read(accountsProvider).valueOrNull ?? [];
    _editing = accounts.cast<Account?>().firstWhere(
        (a) => a?.id == widget.accountId,
        orElse: () => null);
    if (_editing != null) {
      _nameCtrl.text    = _editing!.name;
      _balanceCtrl.text = _editing!.initialBalance.toString();
      _type             = _editing!.type;
      if (_editing!.creditLimit != null) {
        _creditCtrl.text = _editing!.creditLimit.toString();
      }
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);

    final data = {
      'name':            _nameCtrl.text.trim(),
      'type':            _type,
      'initial_balance': double.tryParse(_balanceCtrl.text) ?? 0,
      if (_type == 'credit' && _creditCtrl.text.isNotEmpty)
        'credit_limit': double.tryParse(_creditCtrl.text),
    };

    try {
      if (widget.accountId != null) {
        await ref.read(accountsProvider.notifier).edit(widget.accountId!, data);
      } else {
        await ref.read(accountsProvider.notifier).create(data);
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
    _nameCtrl.dispose(); _balanceCtrl.dispose(); _creditCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_isEditing ? 'Editar cuenta' : 'Nueva cuenta'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 32),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TextFormField(
                controller: _nameCtrl,
                textInputAction: TextInputAction.next,
                decoration: const InputDecoration(
                  labelText: 'Nombre de la cuenta',
                  prefixIcon: Icon(Icons.badge_outlined),
                ),
                validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 20),

              const FormSectionLabel('Tipo de cuenta'),
              Row(
                children: [
                  Expanded(child: FormToggleButton(
                    label: 'Débito',
                    icon: Icons.payments_outlined,
                    selected: _type == 'debit',
                    onTap: () => setState(() => _type = 'debit'))),
                  const SizedBox(width: 10),
                  Expanded(child: FormToggleButton(
                    label: 'Crédito',
                    icon: Icons.credit_card_outlined,
                    color: AppColors.expense,
                    selected: _type == 'credit',
                    onTap: () => setState(() => _type = 'credit'))),
                ],
              ),
              const SizedBox(height: 20),

              TextFormField(
                controller: _balanceCtrl,
                keyboardType:
                    const TextInputType.numberWithOptions(decimal: true),
                decoration: const InputDecoration(
                    labelText: 'Saldo inicial',
                    prefixIcon: Icon(Icons.account_balance_outlined),
                    prefixText: '\$ '),
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Requerido';
                  if (double.tryParse(v) == null) return 'Número inválido';
                  return null;
                },
              ),

              if (_type == 'credit') ...[
                const SizedBox(height: 20),
                TextFormField(
                  controller: _creditCtrl,
                  keyboardType:
                      const TextInputType.numberWithOptions(decimal: true),
                  decoration: const InputDecoration(
                      labelText: 'Límite de crédito (opcional)',
                      prefixIcon: Icon(Icons.speed_outlined),
                      prefixText: '\$ '),
                  validator: (v) {
                    if (v == null || v.isEmpty) return null;
                    if (double.tryParse(v) == null) return 'Número inválido';
                    return null;
                  },
                ),
              ],

              const SizedBox(height: 36),
              SubmitButton(
                loading: _loading,
                onPressed: _submit,
                icon: _isEditing ? Icons.save_outlined : Icons.add,
                label: _isEditing ? 'Actualizar' : 'Crear cuenta',
              ),
            ],
          ),
        ),
      ),
    );
  }
}
