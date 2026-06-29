import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../core/constants.dart';
import '../../../shared/widgets/empty_state.dart';
import '../providers/dashboard_provider.dart';

class DashboardScreen extends ConsumerStatefulWidget {
  const DashboardScreen({super.key});

  @override
  ConsumerState<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends ConsumerState<DashboardScreen>
    with WidgetsBindingObserver {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      ref.invalidate(dashboardProvider);
    }
  }

  final _currency = NumberFormat.currency(locale: 'es_MX', symbol: '\$');

  @override
  Widget build(BuildContext context) {
    final dash = ref.watch(dashboardProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Finanzas'),
        leading: Builder(builder: (ctx) => IconButton(
          icon: const Icon(Icons.menu),
          onPressed: () => Scaffold.of(ctx).openDrawer(),
        )),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: () => ref.invalidate(dashboardProvider),
          ),
        ],
      ),
      body: dash.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => ErrorState(
          message: e.toString(),
          onRetry: () => ref.invalidate(dashboardProvider),
        ),
        data: (data) => RefreshIndicator(
          onRefresh: () async => ref.invalidate(dashboardProvider),
          child: ListView(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
            children: [
              // ── Balance total ──────────────────────────────────────
              _GlassCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Balance Total',
                        style: TextStyle(
                            color: AppColors.textSecondary, fontSize: 13)),
                    const SizedBox(height: 8),
                    Text(
                      _currency.format(data.totalBalance),
                      style: TextStyle(
                        fontSize: 36,
                        fontWeight: FontWeight.w800,
                        color: data.totalBalance >= 0
                            ? AppColors.income
                            : AppColors.expense,
                        letterSpacing: -1,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text('Suscripciones del mes: ${_currency.format(data.monthlySubscriptions)}',
                        style: const TextStyle(
                            color: AppColors.textSecondary, fontSize: 12)),
                  ],
                ),
              ),

              const SizedBox(height: 12),

              // ── Ingresos y Gastos del mes ──────────────────────────
              Row(
                children: [
                  Expanded(
                    child: _GlassCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(children: [
                            const Icon(Icons.arrow_downward,
                                color: AppColors.income, size: 16),
                            const SizedBox(width: 4),
                            const Text('Ingresos',
                                style: TextStyle(
                                    color: AppColors.textSecondary, fontSize: 12)),
                          ]),
                          const SizedBox(height: 6),
                          Text(_currency.format(data.incomeMonth),
                              style: const TextStyle(
                                  color: AppColors.income,
                                  fontSize: 18,
                                  fontWeight: FontWeight.w700)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _GlassCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(children: [
                            const Icon(Icons.arrow_upward,
                                color: AppColors.expense, size: 16),
                            const SizedBox(width: 4),
                            const Text('Gastos',
                                style: TextStyle(
                                    color: AppColors.textSecondary, fontSize: 12)),
                          ]),
                          const SizedBox(height: 6),
                          Text(_currency.format(data.expenseMonth),
                              style: const TextStyle(
                                  color: AppColors.expense,
                                  fontSize: 18,
                                  fontWeight: FontWeight.w700)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 20),

              // ── Movimientos recientes ──────────────────────────────
              const Padding(
                padding: EdgeInsets.only(left: 4, bottom: 8),
                child: Text('Movimientos recientes',
                    style: TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 16,
                        color: AppColors.textPrimary)),
              ),

              if (data.recentTransactions.isEmpty)
                const EmptyState(
                    message: 'Sin movimientos aún',
                    icon: Icons.receipt_long_outlined)
              else
                ...data.recentTransactions.map((m) => _MovementTile(m: m, currency: _currency)),

              const SizedBox(height: 16),

              // ── Acceso rápido ─────────────────────────────────────
              const Padding(
                padding: EdgeInsets.only(left: 4, bottom: 8),
                child: Text('Acceso rápido',
                    style: TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 16,
                        color: AppColors.textPrimary)),
              ),
              _QuickActions(),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Widgets internos ──────────────────────────────────────────────────────

class _GlassCard extends StatelessWidget {
  final Widget child;
  const _GlassCard({required this.child});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: const Border.fromBorderSide(
            BorderSide(color: AppColors.cardBorder)),
      ),
      child: child,
    );
  }
}

class _MovementTile extends StatelessWidget {
  final dynamic m;
  final NumberFormat currency;
  const _MovementTile({required this.m, required this.currency});

  @override
  Widget build(BuildContext context) {
    final isIncome = m.isIncome as bool;
    final color = isIncome ? AppColors.income : AppColors.expense;
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(14),
        border: const Border.fromBorderSide(
            BorderSide(color: AppColors.cardBorder)),
      ),
      child: ListTile(
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
        leading: CircleAvatar(
          backgroundColor: color.withOpacity(0.12),
          child: Icon(
            isIncome ? Icons.arrow_downward : Icons.arrow_upward,
            color: color,
            size: 18,
          ),
        ),
        title: Text(m.description,
            style: const TextStyle(
                fontWeight: FontWeight.w600, fontSize: 14),
            maxLines: 1,
            overflow: TextOverflow.ellipsis),
        subtitle: Text('${m.category} · ${m.account}',
            style: const TextStyle(
                color: AppColors.textSecondary, fontSize: 12)),
        trailing: Text(
          '${isIncome ? '+' : '-'}${currency.format(m.amount)}',
          style: TextStyle(
              color: color,
              fontWeight: FontWeight.w700,
              fontSize: 13),
        ),
      ),
    );
  }
}

class _QuickActions extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        _QBtn(icon: Icons.add_card, label: 'Movimiento',
            onTap: () => context.go('/transactions/add')),
        const SizedBox(width: 8),
        _QBtn(icon: Icons.handshake_outlined, label: 'Préstamos',
            onTap: () => context.go('/loans')),
        const SizedBox(width: 8),
        _QBtn(icon: Icons.subscriptions_outlined, label: 'Suscripciones',
            onTap: () => context.go('/subscriptions')),
      ],
    );
  }
}

class _QBtn extends StatelessWidget {
  final IconData icon;
  final String   label;
  final VoidCallback onTap;
  const _QBtn({required this.icon, required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 14),
          decoration: BoxDecoration(
            color: AppColors.card,
            borderRadius: BorderRadius.circular(14),
            border: const Border.fromBorderSide(
                BorderSide(color: AppColors.cardBorder)),
          ),
          child: Column(
            children: [
              Icon(icon, color: AppColors.primaryLight, size: 24),
              const SizedBox(height: 6),
              Text(label,
                  style: const TextStyle(
                      fontSize: 11, color: AppColors.textSecondary),
                  textAlign: TextAlign.center),
            ],
          ),
        ),
      ),
    );
  }
}
