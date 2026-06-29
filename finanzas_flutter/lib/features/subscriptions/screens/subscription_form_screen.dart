import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants.dart';
import '../../../features/accounts/providers/accounts_provider.dart';
import '../../../shared/widgets/form_widgets.dart';
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

  bool get _isEditing => widget.subscriptionId != null;

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
      showAppSnackBar(context, 'Selecciona una cuenta', isError: true);
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
      if (mounted) showAppSnackBar(context, e.toString(), isError: true);
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
        title: Text(_isEditing ? 'Editar suscripción' : 'Nueva suscripción'),
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
                    labelText: 'Nombre (ej. Netflix)',
                    prefixIcon: Icon(Icons.subscriptions_outlined)),
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
                initialValue: _accountId,
                decoration: const InputDecoration(
                    labelText: 'Cuenta de cobro',
                    prefixIcon: Icon(Icons.account_balance_wallet_outlined)),
                items: accounts.map((a) => DropdownMenuItem(
                    value: a.id, child: Text(a.name))).toList(),
                onChanged: (v) => setState(() => _accountId = v),
                validator: (v) => v == null ? 'Selecciona una cuenta' : null,
              ),
              const SizedBox(height: 20),
              TextFormField(
                controller: _dayCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                    labelText: 'Día de cobro (1–31)',
                    prefixIcon: Icon(Icons.event_outlined)),
                validator: (v) {
                  final n = int.tryParse(v ?? '');
                  if (n == null || n < 1 || n > 31) return '1–31';
                  return null;
                },
              ),
              const SizedBox(height: 20),
              const FormSectionLabel('Ciclo de facturación'),
              Row(children: [
                Expanded(child: FormToggleButton(
                    label: 'Mensual',
                    icon: Icons.event_repeat,
                    selected: _cycle == 'monthly',
                    onTap: () => setState(() => _cycle = 'monthly'))),
                const SizedBox(width: 10),
                Expanded(child: FormToggleButton(
                    label: 'Anual',
                    icon: Icons.calendar_month_outlined,
                    selected: _cycle == 'yearly',
                    onTap: () => setState(() => _cycle = 'yearly'))),
              ]),
              const SizedBox(height: 20),
              const FormSectionLabel('Estado'),
              Row(children: [
                Expanded(child: FormToggleButton(
                    label: 'Activa',
                    icon: Icons.play_circle_outline,
                    selected: _status == 'active',
                    color: AppColors.income,
                    onTap: () => setState(() => _status = 'active'))),
                const SizedBox(width: 10),
                Expanded(child: FormToggleButton(
                    label: 'Pausada',
                    icon: Icons.pause_circle_outline,
                    selected: _status == 'paused',
                    color: AppColors.neutral,
                    onTap: () => setState(() => _status = 'paused'))),
              ]),
              const SizedBox(height: 36),
              SubmitButton(
                loading: _loading,
                onPressed: _submit,
                icon: _isEditing ? Icons.save_outlined : Icons.add,
                label: _isEditing ? 'Actualizar' : 'Crear suscripción',
              ),
            ],
          ),
        ),
      ),
    );
  }
}
