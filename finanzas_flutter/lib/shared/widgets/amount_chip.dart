import 'package:flutter/material.dart';
import '../../core/constants.dart';

class AmountChip extends StatelessWidget {
  final double amount;
  final bool   isIncome;
  final bool   large;

  const AmountChip({
    super.key,
    required this.amount,
    required this.isIncome,
    this.large = false,
  });

  @override
  Widget build(BuildContext context) {
    final color = isIncome ? AppColors.income : AppColors.expense;
    final sign  = isIncome ? '+' : '-';
    return Container(
      padding: EdgeInsets.symmetric(
          horizontal: large ? 12 : 8, vertical: large ? 6 : 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Text(
        '$sign\$${amount.toStringAsFixed(2)}',
        style: TextStyle(
          color: color,
          fontWeight: FontWeight.w700,
          fontSize: large ? 16 : 13,
          letterSpacing: -0.2,
        ),
      ),
    );
  }
}
