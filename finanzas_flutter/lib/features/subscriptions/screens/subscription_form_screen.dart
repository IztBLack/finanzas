import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../features/accounts/providers/accounts_provider.dart';
import '../providers/subscriptions_provider.dart';
import '../models/subscription.dart';

class SubscriptionFormScreen extends ConsumerStatefulWidget {
  final int? subscriptionId;
  const SubscriptionFormScreen({super.key, this.subscriptionId});

  @override
  ConsumerState<SubscriptionFormScreen> createState() =>
      _SubscriptionFormScreenState();
}

class _SubscriptionFormScreenState extends ConsumerState<SubscriptionFormScreen> {
  final _formKey     = GlobalKey<FormState>();
  final _nameCtrl    = TextEditingController();
  final _amountCtrl  = TextEditingController();
  final _dayCtrl     = TextEditingController(text: '1');
  int?   _accountId;
  String _cycle      = 'monthly';
  String _status     = 'active';
  bool   _loading    = false;

  @override
  void initState() {
    super.initState();
    if (widget.subscriptionId != null) {
      final subs = ref.read(subscriptionsProvider).valueOrNull ?? [];
      final sub = subs.cast<Subscription?>()
          .firstWhere((s) => s?.id == widget.subscriptionId, orElse: () => null);
      if (sub != null) {
        _nameCtrl.text   = sub.name;
        _amountCtrl.text = sub.amount.toString();
        _dayCtrl.text    = sub.billingDay.toString();
        _accountId       = sub.accountId;
        _cycle           = sub.billingCycle;
        _status          = sub.status;
      }
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
      'name':          _nameCtrl.text.trim(),
      'amount':        double.tryParse(_amountCtrl.text) ?? 0,
      'account_id':    _accountId,
      'billing_day':   int.tryParse(_dayCtrl.text) ?? 1,
      'billing_cycle': _cycle,
      'status':        _status,
    };
    try {
      if (widget.subscriptionId != null) {
        await ref.read(subscriptionsProvider.notifier).edit(widget.subscriptionId!, data);
      } else {
        await ref.read(subscriptionsProvider.notifier).create(data);
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
    _nameCtrl.dispose(); _amountCtrl.dispose(); _dayCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final accounts = ref.watch(accountsProvider).valueOrNull ?? [];

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.subscriptionId != null
            ? 'Editar suscripción'
            : 'Nueva suscripción'),
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
                decoration: const InputDecoration(labelText: 'Nombre (ej. Netflix)'),
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
                decoration: const InputDecoration(labelText: 'Cuenta de cobro'),
                items: accounts.map((a) => DropdownMenuItem(
                    value: a.id, child: Text(a.name))).toList(),
                onChanged: (v) => setState(() => _accountId = v),
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _dayCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                    labelText: 'Día de cobro (1–31)'),
                validator: (v) {
                  final n = int.tryParse(v ?? '');
                  if (n == null || n < 1 || n > 31) return '1–31';
                  return null;
                },
              ),
              const SizedBox(height: 16),
              const Text('Ciclo de facturación',
                  style: TextStyle(fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              Row(children: [
                Expanded(child: _ToggleBtn(
                    label: 'Mensual', selected: _cycle == 'monthly',
                    onTap: () => setState(() => _cycle = 'monthly'))),
                const SizedBox(width: 10),
                Expanded(child: _ToggleBtn(
                    label: 'Anual', selected: _cycle == 'yearly',
                    onTap: () => setState(() => _cycle = 'yearly'))),
              ]),
              const SizedBox(height: 16),
              const Text('Estado', style: TextStyle(fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              Row(children: [
                Expanded(child: _ToggleBtn(
                    label: 'Activa', selected: _status == 'active',
                    color: const Color(0xFF00BFA5),
                    onTap: () => setState(() => _status = 'active'))),
                const SizedBox(width: 10),
                Expanded(child: _ToggleBtn(
                    label: 'Pausada', selected: _status == 'paused',
                    color: Colors.grey,
                    onTap: () => setState(() => _status = 'paused'))),
              ]),
              const SizedBox(height: 32),
              ElevatedButton(
                onPressed: _loading ? null : _submit,
                child: _loading
                    ? const SizedBox(width: 22, height: 22,
                        child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white))
                    : Text(widget.subscriptionId != null
                        ? 'Actualizar'
                        : 'Crear suscripción'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ToggleBtn extends StatelessWidget {
  final String label;
  final bool selected;
  final Color color;
  final VoidCallback onTap;
  const _ToggleBtn({
    required this.label,
    required this.selected,
    required this.onTap,
    this.color = const Color(0xFF1565C0),
  });

  @override
  Widget build(BuildContext context) => GestureDetector(
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
