import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../core/constants.dart';
import '../../../shared/widgets/confirm_dialog.dart';
import '../../../shared/widgets/empty_state.dart';
import '../../../shared/widgets/glass_card.dart';
import '../../../shared/widgets/list_item_card.dart';
import '../../../shared/widgets/loading_state.dart';
import '../providers/subscriptions_provider.dart';
import '../models/subscription.dart';

class SubscriptionsListScreen extends ConsumerWidget {
  const SubscriptionsListScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state    = ref.watch(subscriptionsProvider);
    final currency = NumberFormat.currency(locale: 'es_MX', symbol: '\$');

    return Scaffold(
      appBar: AppBar(title: const Text('Suscripciones')),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          await context.push('/subscriptions/add');
          ref.read(subscriptionsProvider.notifier).refresh();
        },
        tooltip: 'Nueva suscripción',
        child: const Icon(Icons.add),
      ),
      body: state.when(
        loading: () => const SkeletonList(),
        error: (e, _) => ErrorState(
          message: e.toString(),
          onRetry: () => ref.read(subscriptionsProvider.notifier).refresh(),
        ),
        data: (subs) => RefreshIndicator(
          onRefresh: () => ref.read(subscriptionsProvider.notifier).refresh(),
          child: subs.isEmpty
              ? ListView(
                  children: [
                    SizedBox(
                      height: MediaQuery.of(context).size.height * 0.7,
                      child: EmptyState(
                        message: 'Sin suscripciones registradas',
                        subtitle: 'Da seguimiento a tus pagos recurrentes',
                        icon: Icons.subscriptions_outlined,
                        actionLabel: 'Nueva suscripción',
                        onAction: () async {
                          await context.push('/subscriptions/add');
                          ref.read(subscriptionsProvider.notifier).refresh();
                        },
                      ),
                    ),
                  ],
                )
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
                  itemCount: subs.length,
                  itemBuilder: (ctx, i) {
                    final sub = subs[i];
                    return _SubscriptionTile(
                      sub: sub,
                      currency: currency,
                      onEdit: () async {
                        await context.push('/subscriptions/${sub.id}/edit');
                        ref.read(subscriptionsProvider.notifier).refresh();
                      },
                      onDelete: () async {
                        final ok = await showConfirmDialog(
                          context,
                          title: 'Eliminar suscripción',
                          message: '¿Eliminar "${sub.name}"?',
                        );
                        if (ok) {
                          await ref
                              .read(subscriptionsProvider.notifier)
                              .delete(sub.id);
                        }
                      },
                    );
                  },
                ),
        ),
      ),
    );
  }
}

class _SubscriptionTile extends StatelessWidget {
  final Subscription sub;
  final NumberFormat currency;
  final VoidCallback onEdit;
  final VoidCallback onDelete;

  const _SubscriptionTile({
    required this.sub,
    required this.currency,
    required this.onEdit,
    required this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    final accentColor = sub.isActive ? AppColors.expense : AppColors.neutral;

    return ListItemCard(
      leading: CircleIconBadge(
        icon: Icons.subscriptions_outlined,
        color: accentColor,
      ),
      title: Row(
        children: [
          Flexible(
            child: Text(sub.name,
                style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
                maxLines: 1,
                overflow: TextOverflow.ellipsis),
          ),
          const SizedBox(width: 8),
          StatusBadge(
            label: sub.isActive ? 'ACTIVA' : 'PAUSADA',
            color: sub.isActive ? AppColors.income : AppColors.neutral,
          ),
        ],
      ),
      subtitle: Row(
        children: [
          Icon(
            sub.isMonthly ? Icons.event_repeat : Icons.calendar_month_outlined,
            size: 12,
            color: AppColors.textSecondary,
          ),
          const SizedBox(width: 4),
          Text(
            'Día ${sub.billingDay} · ${sub.isMonthly ? 'Mensual' : 'Anual'}',
            style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
          ),
        ],
      ),
      trailing: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            currency.format(sub.amount),
            style: const TextStyle(
                fontWeight: FontWeight.w700,
                fontSize: 14,
                color: AppColors.expense),
          ),
          PopupMenuButton<String>(
            icon: const Icon(Icons.more_vert, color: AppColors.textSecondary, size: 20),
            onSelected: (action) {
              if (action == 'edit') onEdit();
              if (action == 'delete') onDelete();
            },
            itemBuilder: (_) => const [
              PopupMenuItem(
                  value: 'edit',
                  child: ListTile(
                      leading: Icon(Icons.edit_outlined),
                      title: Text('Editar'),
                      contentPadding: EdgeInsets.zero)),
              PopupMenuItem(
                  value: 'delete',
                  child: ListTile(
                      leading: Icon(Icons.delete_outline, color: AppColors.expense),
                      title: Text('Eliminar', style: TextStyle(color: AppColors.expense)),
                      contentPadding: EdgeInsets.zero)),
            ],
          ),
        ],
      ),
    );
  }
}
