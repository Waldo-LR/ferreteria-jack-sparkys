<?php

$p2b = <<<'HTML'
                <!-- PANEL 3: REGISTRAR PAGO Y GENERAR COMPROBANTE -->
                <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5 shadow-xl space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-700">
                        <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 font-black text-xs flex items-center justify-center">3</span>
                        <h2 class="font-bold text-slate-200 text-sm">Pago & Comprobante SUNAT</h2>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-300 mb-1">Comprobante</label>
                            <select name="tipo_comprobante" id="tipo_comprobante" onchange="cambiarTipoComprobante()" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-200 font-semibold focus:outline-none focus:border-amber-500">
                                <option value="boleta">Boleta de Venta</option>
                                <option value="factura">Factura (IGV 18%)</option>
                                <option value="nota_venta">Nota de Venta / Proforma</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-300 mb-1">Serie Asignada</label>
                            <input type="text" readonly id="display_serie" value="{{ $serieBoleta->serie ?? 'B001' }}" class="w-full bg-slate-900/60 border border-slate-700 rounded-xl p-2.5 text-amber-400 font-bold text-center">
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-300 mb-1">Medio de Pago</label>
                            <select name="medio_pago" id="medio_pago" onchange="calcularVuelto()" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-200 focus:outline-none focus:border-amber-500">
                                <option value="efectivo">Efectivo en Caja</option>
                                <option value="yape">Yape / Plin (Billetera Digital)</option>
                                <option value="deposito">Depósito / Transferencia Bancaria</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-300 mb-1">Paga con (S/)</label>
                                <input type="number" step="0.50" min="0" name="paga_con" id="paga_con" oninput="calcularVuelto()" placeholder="0.00" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-100 font-bold text-sm focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-300 mb-1">Cambio / Vuelto</label>
                                <input type="text" readonly id="display_vuelto" value="S/ 0.00" class="w-full bg-slate-900/80 border border-slate-700 rounded-xl p-2.5 text-emerald-400 font-extrabold text-sm text-center">
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900 p-3 rounded-xl border border-slate-700/80 space-y-1.5 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>Subtotal / Op. Gravada:</span>
                            <span id="displaySubtotal">S/ 0.00</span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>IGV (18%):</span>
                            <span id="displayIGV">S/ 0.00</span>
                        </div>
                        <div class="flex justify-between text-slate-100 font-extrabold text-sm pt-1 border-t border-slate-800">
                            <span>Total Pagar:</span>
                            <span id="displayTotalPagar" class="text-emerald-400">S/ 0.00</span>
                        </div>
                    </div>

                    <div class="pt-2 space-y-2">
                        <button type="submit" class="w-full py-3.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black rounded-xl text-sm transition shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Guardar Venta & Generar Comprobante</span>
                        </button>
                    </div>

                </div>

            </div>

        </div>
    </form>
</div>
HTML;

file_put_contents(__DIR__ . '/resources/views/ventas/pos.blade.php', $p2b, FILE_APPEND);
echo "pos p2b done\n";
