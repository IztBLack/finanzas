import 'package:flutter/material.dart';
import '../../core/constants.dart';

/// Spinner centrado consistente para pantallas completas en carga inicial.
class LoadingState extends StatelessWidget {
  const LoadingState({super.key});

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: SizedBox(
        width: 32,
        height: 32,
        child: CircularProgressIndicator(
          strokeWidth: 2.5,
          color: AppColors.primary,
        ),
      ),
    );
  }
}

/// Lista de tarjetas "skeleton" con efecto shimmer, para listas
/// (cuentas, movimientos, préstamos, suscripciones, categorías).
class SkeletonList extends StatefulWidget {
  final int itemCount;
  final double itemHeight;

  const SkeletonList({super.key, this.itemCount = 6, this.itemHeight = 76});

  @override
  State<SkeletonList> createState() => _SkeletonListState();
}

class _SkeletonListState extends State<SkeletonList>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    )..repeat();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
      physics: const NeverScrollableScrollPhysics(),
      itemCount: widget.itemCount,
      itemBuilder: (ctx, i) => Padding(
        padding: const EdgeInsets.only(bottom: 10),
        child: AnimatedBuilder(
          animation: _controller,
          builder: (context, _) {
            final t = _controller.value;
            final opacity = 0.35 + 0.25 * (1 - (t - 0.5).abs() * 2);
            return Container(
              height: widget.itemHeight,
              decoration: BoxDecoration(
                color: AppColors.card,
                borderRadius: BorderRadius.circular(16),
                border: const Border.fromBorderSide(
                    BorderSide(color: AppColors.cardBorder)),
              ),
              child: Padding(
                padding: const EdgeInsets.symmetric(
                    horizontal: 14, vertical: 14),
                child: Row(
                  children: [
                    _block(40, 40, opacity, circle: true),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          _block(double.infinity, 14, opacity),
                          const SizedBox(height: 8),
                          _block(120, 11, opacity),
                        ],
                      ),
                    ),
                    const SizedBox(width: 12),
                    _block(56, 16, opacity),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _block(double width, double height, double opacity,
      {bool circle = false}) {
    return Container(
      width: width,
      height: height,
      decoration: BoxDecoration(
        color: AppColors.skeleton.withOpacity(opacity),
        borderRadius: circle ? null : BorderRadius.circular(6),
        shape: circle ? BoxShape.circle : BoxShape.rectangle,
      ),
    );
  }
}
