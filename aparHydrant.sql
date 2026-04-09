GO


/****** Object:  Table [dbo].[SE_FIRE_PROTECTION_MASTER] ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[SE_FIRE_PROTECTION_MASTER](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[asset_code] [nvarchar](255) NOT NULL UNIQUE,
	[asset_type] [nvarchar](50) NOT NULL, -- 'APAR' or 'Hydrant'
	[area] [nvarchar](255) NOT NULL,
	[location] [nvarchar](max) NULL,
	[status] [nvarchar](50) NOT NULL DEFAULT 'OK',
	[model_type] [nvarchar](255) NULL, -- e.g. CO2, Powder
	[weight] [nvarchar](50) NULL,
	[expired_date] [date] NULL,
	[last_inspection_date] [datetime] NULL,
	[x_coordinate] [decimal](8, 2) NULL,
	[y_coordinate] [decimal](8, 2) NULL,
	[pic_empid] [varchar](10) NULL,
	[is_active] [bit] NOT NULL DEFAULT 1,
	[created_at] [datetime] DEFAULT GETDATE(),
	[updated_at] [datetime] DEFAULT GETDATE(),
PRIMARY KEY CLUSTERED ([id] ASC)) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[SE_FIRE_PROTECTION_TRANS] ******/
CREATE TABLE [dbo].[SE_FIRE_PROTECTION_TRANS](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[asset_id] [bigint] NOT NULL,
	[user_id] [varchar](5) NULL,
	[inspection_date] [datetime] NOT NULL DEFAULT GETDATE(),
	[photo_general] [nvarchar](255) NULL,
	[notes] [nvarchar](max) NULL,
    -- APAR FLAT COLS
	[exp_date_ok] [bit] NULL, [exp_date_foto] [nvarchar](255) NULL,
	[pressure_ok] [bit] NULL, [pressure_foto] [nvarchar](255) NULL,
	[weight_co2_ok] [bit] NULL, [weight_co2_foto] [nvarchar](255) NULL,
	[tube_ok] [bit] NULL, [tube_foto] [nvarchar](255) NULL,
	[hose_ok] [bit] NULL, [hose_foto] [nvarchar](255) NULL,
	[bracket_ok] [bit] NULL, [bracket_foto] [nvarchar](255) NULL,
	[wi_ok] [bit] NULL, [wi_foto] [nvarchar](255) NULL,
	[form_kejadian_ok] [bit] NULL, [form_kejadian_foto] [nvarchar](255) NULL,
	[sign_box_ok] [bit] NULL, [sign_box_foto] [nvarchar](255) NULL,
	[sign_triangle_ok] [bit] NULL, [sign_triangle_foto] [nvarchar](255) NULL,
	[marking_tiger_ok] [bit] NULL, [marking_tiger_foto] [nvarchar](255) NULL,
	[marking_beam_ok] [bit] NULL, [marking_beam_foto] [nvarchar](255) NULL,
	[sr_apar_ok] [bit] NULL, [sr_apar_foto] [nvarchar](255) NULL,
	[kocok_apar_ok] [bit] NULL, [kocok_apar_foto] [nvarchar](255) NULL,
    [label_ok] [bit] NULL, [label_foto] [nvarchar](255) NULL,
    -- HYDRANT FLAT COLS
    [body_hydrant_ok] [bit] NULL, [body_hydrant_foto] [nvarchar](255) NULL,
	[selang_ok] [bit] NULL, [selang_foto] [nvarchar](255) NULL,
	[couple_join_ok] [bit] NULL, [couple_join_foto] [nvarchar](255) NULL,
	[nozzle_ok] [bit] NULL, [nozzle_foto] [nvarchar](255) NULL,
	[check_sheet_ok] [bit] NULL, [check_sheet_foto] [nvarchar](255) NULL,
	[valve_kran_ok] [bit] NULL, [valve_kran_foto] [nvarchar](255) NULL,
	[lampu_ok] [bit] NULL, [lampu_foto] [nvarchar](255) NULL,
	[cover_lampu_ok] [bit] NULL, [cover_lampu_foto] [nvarchar](255) NULL,
	[box_display_ok] [bit] NULL, [box_display_foto] [nvarchar](255) NULL,
	[konsul_hydrant_ok] [bit] NULL, [konsul_hydrant_foto] [nvarchar](255) NULL,
	[jr_ok] [bit] NULL, [jr_foto] [nvarchar](255) NULL,
	[marking_ok] [bit] NULL, [marking_foto] [nvarchar](255) NULL,
    [kunci_pilar_hydrant_ok] [bit] NULL, [kunci_pilar_hydrant_foto] [nvarchar](max) NULL,
	[pilar_hydrant_ok] [bit] NULL, [pilar_hydrant_foto] [nvarchar](max) NULL,
	[sign_larangan_ok] [bit] NULL, [sign_larangan_foto] [nvarchar](max) NULL,
	[nomor_hydrant_ok] [bit] NULL, [nomor_hydrant_foto] [nvarchar](max) NULL,
	[wi_hydrant_ok] [bit] NULL, [wi_hydrant_foto] [nvarchar](max) NULL,
PRIMARY KEY CLUSTERED ([id] ASC)) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[SE_FIRE_PROTECTION_LINES] (Audit Trail) ******/
CREATE TABLE [dbo].[SE_FIRE_PROTECTION_LINES](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[trans_id] [bigint] NULL,
	[asset_id] [bigint] NOT NULL,
	[check_item_alias] [nvarchar](255) NOT NULL,
	[finding_desc] [nvarchar](max) NULL,
	[countermeasure] [nvarchar](max) NULL,
	[repair_status] [nvarchar](50) NOT NULL DEFAULT 'Open', -- Open, On Progress, Closed, Verified
	[photo_evidence] [nvarchar](255) NULL,
	[repair_photo] [nvarchar](255) NULL,
	[due_date] [date] NULL,
	[verified_at] [datetime] NULL,
	[verified_by] [nvarchar](20) NULL,
	[created_at] [datetime] DEFAULT GETDATE(),
	[updated_at] [datetime] DEFAULT GETDATE(),
PRIMARY KEY CLUSTERED ([id] ASC)) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[SE_FIRE_PROTECTION_AREA] ******/
CREATE TABLE [dbo].[SE_FIRE_PROTECTION_AREA](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[empid] [varchar](5) NOT NULL,
	[asset_type] [varchar](10) NOT NULL,
	[area_name] [varchar](50) NOT NULL,
PRIMARY KEY CLUSTERED ([id] ASC)) ON [PRIMARY]
GO

-- FK Relations
ALTER TABLE [dbo].[SE_FIRE_PROTECTION_TRANS] WITH CHECK ADD FOREIGN KEY([asset_id]) REFERENCES [dbo].[SE_FIRE_PROTECTION_MASTER] ([id]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[SE_FIRE_PROTECTION_LINES] WITH CHECK ADD FOREIGN KEY([asset_id]) REFERENCES [dbo].[SE_FIRE_PROTECTION_MASTER] ([id]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[SE_FIRE_PROTECTION_LINES] WITH CHECK ADD FOREIGN KEY([trans_id]) REFERENCES [dbo].[SE_FIRE_PROTECTION_TRANS] ([id])
GO

-- Keep existing identity tables (HRD + Users)
/****** Object:  Schema [Users] ******/
IF NOT EXISTS (SELECT * FROM sys.schemas WHERE name = 'Users')
BEGIN
    EXEC('CREATE SCHEMA [Users]')
END
GO
-- (Assuming HRD_EMPLOYEE_TABLE and Users.UserTable exist in the environment)
