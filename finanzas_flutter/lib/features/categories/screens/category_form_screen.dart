import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants.dart';
import '../../../shared/widgets/form_widgets.dart';
import '../providers/categories_provider.dart';
import '../models/category.dart';

class CategoryFormScreen extends ConsumerStatefulWidget {
  final int? categoryId;
  const CategoryFormScreen({super.key, this.categoryId});

  @override
  ConsumerState<CategoryFormScreen> createState() => _CategoryFormScreenState();
}

class _CategoryFormScreenState extends ConsumerState<CategoryFormScreen> {
  final _formKey  = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  String _type    = 'expense';
  bool   _loading = false;

  bool get _isEditing => widget.categoryId != null;

  @override
  void initState() {
    super.initState();
    if (widget.categoryId != null) {
      final cats = ref.read(categoriesProvider).valueOrNull ?? [];
      final cat = cats.cast<Category?>()
          .firstWhere((c) => c?.id == widget.categoryId, orElse: () => null);
      if (cat != null) {
        _nameCtrl.text = cat.name;
        _type          = cat.type;
      }
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);
    try {
      final data = {'name': _nameCtrl.text.trim(), 'type': _type};
      if (widget.categoryId != null) {
        await ref.read(categoriesProvider.notifier).edit(widget.categoryId!, data);
      } else {
        await ref.read(categoriesProvider.notifier).create(data);
      }
      if (mounted) Navigator.of(context).pop(true);
    } catch (e) {
      if (mounted) showAppSnackBar(context, e.toString(), isError: true);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  void dispose() { _nameCtrl.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_isEditing ? 'Editar categoría' : 'Nueva categoría'),
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
                textInputAction: TextInputAction.done,
                decoration: const InputDecoration(
                  labelText: 'Nombre',
                  prefixIcon: Icon(Icons.label_outline),
                ),
                validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 20),
              const FormSectionLabel('Tipo'),
              Row(children: [
                Expanded(child: FormToggleButton(
                    label: 'Gasto',
                    icon: Icons.arrow_upward,
                    selected: _type == 'expense',
                    color: AppColors.expense,
                    onTap: () => setState(() => _type = 'expense'))),
                const SizedBox(width: 10),
                Expanded(child: FormToggleButton(
                    label: 'Ingreso',
                    icon: Icons.arrow_downward,
                    selected: _type == 'income',
                    color: AppColors.income,
                    onTap: () => setState(() => _type = 'income'))),
              ]),
              const SizedBox(height: 36),
              SubmitButton(
                loading: _loading,
                onPressed: _submit,
                icon: _isEditing ? Icons.save_outlined : Icons.add,
                label: _isEditing ? 'Actualizar' : 'Crear categoría',
              ),
            ],
          ),
        ),
      ),
    );
  }
}
