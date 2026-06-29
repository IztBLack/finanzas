import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
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

  @override
  void initState() {
    super.initState();
    if (widget.accountId != null) _loadAccount();
  }

  void _loadAccount() {
    final accounts = ref.read(accountsProvider).valueOrNull ?? [];
    _editing = accounts.firstWhere((a) => a.id == widget.accountId,
        orElse: () => accounts.first);
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
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString())));
      }
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
        title: Text(widget.accountId != null ? 'Editar cuenta' : 'Nueva cuenta'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TextFormField(
                controller: _nameCtrl,
                decoration: const InputDecoration(labelText: 'Nombre de la cuenta'),
                validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 16),

              // Tipo
              const Text('Tipo de cuenta',
                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(child: _TypeBtn(
                    label: 'Débito', selected: _type == 'debit',
                    onTap: () => setState(() => _type = 'debit'))),
                  const SizedBox(width: 10),
                  Expanded(child: _TypeBtn(
                    label: 'Crédito', selected: _type == 'credit',
                    onTap: () => setState(() => _type = 'credit'))),
                ],
              ),
              const SizedBox(height: 16),

              TextFormField(
                controller: _balanceCtrl,
                keyboardType:
                    const TextInputType.numberWithOptions(decimal: true),
                decoration: const InputDecoration(
                    labelText: 'Saldo inicial', prefixText: '\$ '),
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Requerido';
                  if (double.tryParse(v) == null) return 'Número inválido';
                  return null;
                },
              ),

              if (_type == 'credit') ...[
                const SizedBox(height: 16),
                TextFormField(
                  controller: _creditCtrl,
                  keyboardType:
                      const TextInputType.numberWithOptions(decimal: true),
                  decoration: const InputDecoration(
                      labelText: 'Límite de crédito', prefixText: '\$ '),
                ),
              ],

              const SizedBox(height: 32),
              ElevatedButton(
                onPressed: _loading ? null : _submit,
                child: _loading
                    ? const SizedBox(width: 22, height: 22,
                        child: CircularProgressIndicator(
                            strokeWidth: 2.5, color: Colors.white))
                    : Text(widget.accountId != null ? 'Actualizar' : 'Crear cuenta'),
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
  final VoidCallback onTap;
  const _TypeBtn({required this.label, required this.selected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: selected
              ? const Color(0xFF1565C0).withOpacity(0.2)
              : const Color(0xFF1C2132),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: selected ? const Color(0xFF1565C0) : const Color(0xFF262D42),
            width: selected ? 2 : 1,
          ),
        ),
        child: Text(label,
            textAlign: TextAlign.center,
            style: TextStyle(
                fontWeight: FontWeight.w600,
                color: selected
                    ? const Color(0xFF1565C0)
                    : const Color(0xFF8A93A8))),
      ),
    );
  }
}
