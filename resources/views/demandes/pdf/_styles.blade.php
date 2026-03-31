<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1e293b; background: #fff; }
    .page { padding: 28px 32px 90px 32px; position: relative; min-height: 100%; }
    .header { margin-bottom: 22px; padding-bottom: 14px; border-bottom: 3px solid #0f4c91; }
    .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
    .header-left { flex: 1; }
    .header-left .republic { font-size: 8.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
    .header-left .institution { font-size: 15px; font-weight: bold; color: #0f4c91; text-transform: uppercase; letter-spacing: 0.3px; }
    .header-left .subtitle { font-size: 9px; color: #64748b; margin-top: 2px; }
    .header-right { text-align: right; }
    .doc-type-badge { display: inline-block; background: #0f4c91; color: #fff; font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.6px; padding: 3px 10px; border-radius: 3px; margin-bottom: 6px; }
    .ref-block { font-size: 8.5px; color: #475569; line-height: 1.7; }
    .ref-block strong { color: #1e293b; }
    .status-bar { background: #f1f5f9; border-left: 4px solid #0f4c91; padding: 7px 12px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; }
    .status-bar .status-label { font-size: 9px; color: #64748b; text-transform: uppercase; }
    .status-bar .status-value { font-size: 10px; font-weight: bold; color: #0f4c91; }
    .status-bar .date-info { font-size: 8.5px; color: #64748b; text-align: right; }
    .annotation-box { border: 1px solid #f59e0b; background: #fffbeb; border-radius: 4px; padding: 10px 12px; margin-bottom: 18px; }
    .annotation-header { display: flex; align-items: center; margin-bottom: 6px; }
    .annotation-badge { background: #f59e0b; color: #fff; font-size: 8px; font-weight: bold; text-transform: uppercase; padding: 2px 7px; border-radius: 2px; margin-right: 8px; }
    .annotation-folder { font-size: 8.5px; color: #92400e; font-style: italic; }
    .annotation-text { font-size: 10px; color: #78350f; line-height: 1.5; }
    .annotation-meta { font-size: 8px; color: #b45309; margin-top: 5px; }
    .section { margin-bottom: 18px; }
    .section-header { background: #0f4c91; color: #fff; padding: 5px 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0; border-radius: 2px 2px 0 0; }
    .section-body { border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 2px 2px; overflow: hidden; }
    table.info { width: 100%; border-collapse: collapse; }
    table.info tr:nth-child(even) td { background: #f8fafc; }
    table.info td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; line-height: 1.4; }
    table.info tr:last-child td { border-bottom: none; }
    table.info td.label { font-weight: bold; font-size: 9px; color: #475569; text-transform: uppercase; letter-spacing: 0.3px; width: 35%; background: #f1f5f9; }
    table.info td.value { font-size: 10px; color: #1e293b; }
    table.info-2col td.label { width: 22%; }
    table.docs { width: 100%; border-collapse: collapse; }
    table.docs th { background: #e2e8f0; font-size: 8.5px; font-weight: bold; color: #475569; text-transform: uppercase; padding: 5px 8px; text-align: left; border-bottom: 1px solid #cbd5e1; }
    table.docs td { padding: 5px 8px; font-size: 9.5px; border-bottom: 1px solid #e2e8f0; color: #1e293b; }
    table.docs tr:last-child td { border-bottom: none; }
    table.docs tr:nth-child(even) td { background: #f8fafc; }
    .signature-zone { margin-top: 22px; display: flex; justify-content: space-between; }
    .sig-block { width: 42%; border-top: 1px solid #94a3b8; padding-top: 6px; text-align: center; }
    .sig-block .sig-label { font-size: 8.5px; color: #64748b; text-transform: uppercase; }
    .sig-block .sig-name { font-size: 9.5px; font-weight: bold; color: #1e293b; margin-top: 4px; }
    .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 8px 32px; background: #f8fafc; border-top: 2px solid #0f4c91; display: flex; justify-content: space-between; align-items: center; }
    .footer-left { font-size: 7.5px; color: #64748b; }
    .footer-center { font-size: 7.5px; color: #94a3b8; font-style: italic; text-align: center; }
    .footer-right { font-size: 7.5px; color: #64748b; text-align: right; }
    .confidential { position: fixed; top: 45%; left: 10%; width: 80%; text-align: center; font-size: 55px; font-weight: bold; color: rgba(15, 76, 145, 0.04); text-transform: uppercase; letter-spacing: 10px; transform: rotate(-30deg); pointer-events: none; }
</style>
