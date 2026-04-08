USE [master]
GO
/****** Object:  Database [ATI]    Script Date: 08/04/2026 06:53:19 ******/
CREATE DATABASE [ATI]
 CONTAINMENT = NONE
 ON  PRIMARY 
( NAME = N'ATI', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL16.SQLEXPRESS\MSSQL\DATA\ATI.mdf' , SIZE = 73728KB , MAXSIZE = UNLIMITED, FILEGROWTH = 65536KB )
 LOG ON 
( NAME = N'ATI_log', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL16.SQLEXPRESS\MSSQL\DATA\ATI_log.ldf' , SIZE = 8192KB , MAXSIZE = 2048GB , FILEGROWTH = 65536KB )
 WITH CATALOG_COLLATION = DATABASE_DEFAULT, LEDGER = OFF
GO
ALTER DATABASE [ATI] SET COMPATIBILITY_LEVEL = 160
GO
IF (1 = FULLTEXTSERVICEPROPERTY('IsFullTextInstalled'))
begin
EXEC [ATI].[dbo].[sp_fulltext_database] @action = 'enable'
end
GO
ALTER DATABASE [ATI] SET ANSI_NULL_DEFAULT OFF 
GO
ALTER DATABASE [ATI] SET ANSI_NULLS OFF 
GO
ALTER DATABASE [ATI] SET ANSI_PADDING OFF 
GO
ALTER DATABASE [ATI] SET ANSI_WARNINGS OFF 
GO
ALTER DATABASE [ATI] SET ARITHABORT OFF 
GO
ALTER DATABASE [ATI] SET AUTO_CLOSE OFF 
GO
ALTER DATABASE [ATI] SET AUTO_SHRINK OFF 
GO
ALTER DATABASE [ATI] SET AUTO_UPDATE_STATISTICS ON 
GO
ALTER DATABASE [ATI] SET CURSOR_CLOSE_ON_COMMIT OFF 
GO
ALTER DATABASE [ATI] SET CURSOR_DEFAULT  GLOBAL 
GO
ALTER DATABASE [ATI] SET CONCAT_NULL_YIELDS_NULL OFF 
GO
ALTER DATABASE [ATI] SET NUMERIC_ROUNDABORT OFF 
GO
ALTER DATABASE [ATI] SET QUOTED_IDENTIFIER OFF 
GO
ALTER DATABASE [ATI] SET RECURSIVE_TRIGGERS OFF 
GO
ALTER DATABASE [ATI] SET  DISABLE_BROKER 
GO
ALTER DATABASE [ATI] SET AUTO_UPDATE_STATISTICS_ASYNC OFF 
GO
ALTER DATABASE [ATI] SET DATE_CORRELATION_OPTIMIZATION OFF 
GO
ALTER DATABASE [ATI] SET TRUSTWORTHY OFF 
GO
ALTER DATABASE [ATI] SET ALLOW_SNAPSHOT_ISOLATION OFF 
GO
ALTER DATABASE [ATI] SET PARAMETERIZATION SIMPLE 
GO
ALTER DATABASE [ATI] SET READ_COMMITTED_SNAPSHOT OFF 
GO
ALTER DATABASE [ATI] SET HONOR_BROKER_PRIORITY OFF 
GO
ALTER DATABASE [ATI] SET RECOVERY SIMPLE 
GO
ALTER DATABASE [ATI] SET  MULTI_USER 
GO
ALTER DATABASE [ATI] SET PAGE_VERIFY CHECKSUM  
GO
ALTER DATABASE [ATI] SET DB_CHAINING OFF 
GO
ALTER DATABASE [ATI] SET FILESTREAM( NON_TRANSACTED_ACCESS = OFF ) 
GO
ALTER DATABASE [ATI] SET TARGET_RECOVERY_TIME = 60 SECONDS 
GO
ALTER DATABASE [ATI] SET DELAYED_DURABILITY = DISABLED 
GO
ALTER DATABASE [ATI] SET ACCELERATED_DATABASE_RECOVERY = OFF  
GO
ALTER DATABASE [ATI] SET QUERY_STORE = ON
GO
ALTER DATABASE [ATI] SET QUERY_STORE (OPERATION_MODE = READ_WRITE, CLEANUP_POLICY = (STALE_QUERY_THRESHOLD_DAYS = 30), DATA_FLUSH_INTERVAL_SECONDS = 900, INTERVAL_LENGTH_MINUTES = 60, MAX_STORAGE_SIZE_MB = 1000, QUERY_CAPTURE_MODE = AUTO, SIZE_BASED_CLEANUP_MODE = AUTO, MAX_PLANS_PER_QUERY = 200, WAIT_STATS_CAPTURE_MODE = ON)
GO
USE [ATI]
GO
/****** Object:  Schema [Users]    Script Date: 08/04/2026 06:53:20 ******/
CREATE SCHEMA [Users]
GO
/****** Object:  Table [dbo].[apar_abnormal_cases]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[apar_abnormal_cases](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[apar_id] [bigint] NULL,
	[inspection_id] [bigint] NULL,
	[abnormal_case] [nvarchar](max) NULL,
	[countermeasure] [nvarchar](max) NULL,
	[due_date] [date] NULL,
	[pic_id] [bigint] NULL,
	[status] [nvarchar](255) NOT NULL,
	[verified_at] [datetime] NULL,
	[verified_by] [bigint] NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[repair_photo] [nvarchar](255) NULL,
	[new_expired_date] [date] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[apar_repairs]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[apar_repairs](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[apar_id] [bigint] NOT NULL,
	[user_id] [varchar](5) NULL,
	[description] [nvarchar](max) NOT NULL,
	[status_after_repair] [nvarchar](255) NOT NULL,
	[photo] [nvarchar](255) NULL,
	[notes] [nvarchar](max) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[due_date] [date] NULL,
	[is_approved] [bit] NOT NULL,
	[progress] [int] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[apars]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[apars](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[code] [nvarchar](255) NOT NULL,
	[location] [nvarchar](255) NOT NULL,
	[area] [nvarchar](255) NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[last_inspection_date] [datetime] NULL,
	[status] [nvarchar](255) NOT NULL,
	[expired_date] [date] NULL,
	[type] [nvarchar](255) NULL,
	[weight] [nvarchar](255) NULL,
	[x_coordinate] [decimal](5, 2) NULL,
	[y_coordinate] [decimal](5, 2) NULL,
	[is_active] [bit] NOT NULL,
	[pic_empid] [varchar](10) NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[bimonthly_apar_inspections]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[bimonthly_apar_inspections](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[apar_id] [bigint] NOT NULL,
	[user_id] [varchar](5) NULL,
	[inspection_date] [datetime] NOT NULL,
	[photo] [nvarchar](255) NULL,
	[notes] [nvarchar](max) NULL,
	[exp_date_ok] [bit] NOT NULL,
	[exp_date_foto] [nvarchar](255) NULL,
	[pressure_ok] [bit] NOT NULL,
	[pressure_foto] [nvarchar](255) NULL,
	[weight_co2_ok] [bit] NOT NULL,
	[weight_co2_foto] [nvarchar](255) NULL,
	[tube_ok] [bit] NOT NULL,
	[tube_foto] [nvarchar](255) NULL,
	[hose_ok] [bit] NOT NULL,
	[hose_foto] [nvarchar](255) NULL,
	[bracket_ok] [bit] NOT NULL,
	[bracket_foto] [nvarchar](255) NULL,
	[wi_ok] [bit] NOT NULL,
	[wi_foto] [nvarchar](255) NULL,
	[form_kejadian_ok] [bit] NOT NULL,
	[form_kejadian_foto] [nvarchar](255) NULL,
	[sign_box_ok] [bit] NOT NULL,
	[sign_box_foto] [nvarchar](255) NULL,
	[sign_triangle_ok] [bit] NOT NULL,
	[sign_triangle_foto] [nvarchar](255) NULL,
	[marking_tiger_ok] [bit] NOT NULL,
	[marking_tiger_foto] [nvarchar](255) NULL,
	[marking_beam_ok] [bit] NOT NULL,
	[marking_beam_foto] [nvarchar](255) NULL,
	[sr_apar_ok] [bit] NOT NULL,
	[sr_apar_foto] [nvarchar](255) NULL,
	[kocok_apar_ok] [bit] NOT NULL,
	[kocok_apar_foto] [nvarchar](255) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[exp_date_keterangan] [nvarchar](255) NULL,
	[kocok_apar_keterangan] [nvarchar](255) NULL,
	[label_ok] [bit] NOT NULL,
	[label_foto] [nvarchar](255) NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[bimonthly_hydrant_inspections]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[bimonthly_hydrant_inspections](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[hydrant_id] [bigint] NOT NULL,
	[user_id] [varchar](5) NULL,
	[inspection_date] [datetime] NOT NULL,
	[photo] [nvarchar](255) NULL,
	[notes] [nvarchar](max) NULL,
	[jenis_hydrant] [nvarchar](255) NULL,
	[body_hydrant_ok] [bit] NOT NULL,
	[body_hydrant_foto] [nvarchar](255) NULL,
	[selang_ok] [bit] NOT NULL,
	[selang_foto] [nvarchar](255) NULL,
	[couple_join_ok] [bit] NOT NULL,
	[couple_join_foto] [nvarchar](255) NULL,
	[nozzle_ok] [bit] NOT NULL,
	[nozzle_foto] [nvarchar](255) NULL,
	[check_sheet_ok] [bit] NOT NULL,
	[check_sheet_foto] [nvarchar](255) NULL,
	[valve_kran_ok] [bit] NOT NULL,
	[valve_kran_foto] [nvarchar](255) NULL,
	[lampu_ok] [bit] NOT NULL,
	[lampu_foto] [nvarchar](255) NULL,
	[cover_lampu_ok] [bit] NOT NULL,
	[cover_lampu_foto] [nvarchar](255) NULL,
	[box_display_ok] [bit] NOT NULL,
	[box_display_foto] [nvarchar](255) NULL,
	[konsul_hydrant_ok] [bit] NOT NULL,
	[konsul_hydrant_foto] [nvarchar](255) NULL,
	[jr_ok] [bit] NOT NULL,
	[jr_foto] [nvarchar](255) NULL,
	[marking_ok] [bit] NOT NULL,
	[marking_foto] [nvarchar](255) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[label_ok] [bit] NOT NULL,
	[label_foto] [nvarchar](255) NULL,
	[kunci_pilar_hydrant_ok] [int] NULL,
	[kunci_pilar_hydrant_foto] [nvarchar](max) NULL,
	[pilar_hydrant_ok] [int] NULL,
	[pilar_hydrant_foto] [nvarchar](max) NULL,
	[sign_larangan_ok] [int] NULL,
	[sign_larangan_foto] [nvarchar](max) NULL,
	[nomor_hydrant_ok] [int] NULL,
	[nomor_hydrant_foto] [nvarchar](max) NULL,
	[wi_hydrant_ok] [int] NULL,
	[wi_hydrant_foto] [nvarchar](max) NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[bimonthly_inspections]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[bimonthly_inspections](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[apar_unit_id] [bigint] NULL,
	[user_id] [varchar](5) NULL,
	[inspection_date] [datetime] NOT NULL,
	[exp_date_ok] [bit] NOT NULL,
	[pressure_ok] [bit] NOT NULL,
	[weight_co2_ok] [bit] NOT NULL,
	[tube_ok] [bit] NOT NULL,
	[hose_ok] [bit] NOT NULL,
	[bracket_ok] [bit] NOT NULL,
	[wi_ok] [bit] NOT NULL,
	[form_kejadian_ok] [bit] NOT NULL,
	[sign_box_ok] [bit] NOT NULL,
	[sign_triangle_ok] [bit] NOT NULL,
	[marking_tiger_ok] [bit] NOT NULL,
	[marking_beam_ok] [bit] NOT NULL,
	[sr_apar_ok] [bit] NOT NULL,
	[kocok_apar_ok] [bit] NOT NULL,
	[exp_date_foto] [nvarchar](max) NULL,
	[pressure_foto] [nvarchar](max) NULL,
	[weight_co2_foto] [nvarchar](max) NULL,
	[tube_foto] [nvarchar](max) NULL,
	[hose_foto] [nvarchar](max) NULL,
	[bracket_foto] [nvarchar](max) NULL,
	[wi_foto] [nvarchar](max) NULL,
	[form_kejadian_foto] [nvarchar](max) NULL,
	[sign_box_foto] [nvarchar](max) NULL,
	[sign_triangle_foto] [nvarchar](max) NULL,
	[marking_tiger_foto] [nvarchar](max) NULL,
	[marking_beam_foto] [nvarchar](max) NULL,
	[sr_apar_foto] [nvarchar](max) NULL,
	[kocok_apar_foto] [nvarchar](max) NULL,
	[notes] [nvarchar](max) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[hydrant_unit_id] [bigint] NULL,
	[jenis_hydrant] [nvarchar](255) NULL,
	[body_hydrant_ok] [bit] NOT NULL,
	[selang_ok] [bit] NOT NULL,
	[couple_join_ok] [bit] NOT NULL,
	[nozzle_ok] [bit] NOT NULL,
	[check_sheet_ok] [bit] NOT NULL,
	[valve_kran_ok] [bit] NOT NULL,
	[lampu_ok] [bit] NOT NULL,
	[cover_lampu_ok] [bit] NOT NULL,
	[box_display_ok] [bit] NOT NULL,
	[konsul_hydrant_ok] [bit] NOT NULL,
	[jr_ok] [bit] NOT NULL,
	[marking_ok] [bit] NOT NULL,
	[body_hydrant_foto] [nvarchar](max) NULL,
	[selang_foto] [nvarchar](max) NULL,
	[couple_join_foto] [nvarchar](max) NULL,
	[nozzle_foto] [nvarchar](max) NULL,
	[check_sheet_foto] [nvarchar](max) NULL,
	[valve_kran_foto] [nvarchar](max) NULL,
	[lampu_foto] [nvarchar](max) NULL,
	[cover_lampu_foto] [nvarchar](max) NULL,
	[box_display_foto] [nvarchar](max) NULL,
	[konsul_hydrant_foto] [nvarchar](max) NULL,
	[jr_foto] [nvarchar](max) NULL,
	[marking_foto] [nvarchar](max) NULL,
	[photo] [nvarchar](255) NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[cache]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[cache](
	[key] [nvarchar](255) NOT NULL,
	[value] [nvarchar](max) NOT NULL,
	[expiration] [int] NOT NULL,
 CONSTRAINT [cache_key_primary] PRIMARY KEY CLUSTERED 
(
	[key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[cache_locks]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[cache_locks](
	[key] [nvarchar](255) NOT NULL,
	[owner] [nvarchar](255) NOT NULL,
	[expiration] [int] NOT NULL,
 CONSTRAINT [cache_locks_key_primary] PRIMARY KEY CLUSTERED 
(
	[key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[failed_jobs]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[failed_jobs](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[uuid] [nvarchar](255) NOT NULL,
	[connection] [nvarchar](max) NOT NULL,
	[queue] [nvarchar](max) NOT NULL,
	[payload] [nvarchar](max) NOT NULL,
	[exception] [nvarchar](max) NOT NULL,
	[failed_at] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[HRD_EMPLOYEE_TABLE]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[HRD_EMPLOYEE_TABLE](
	[EmpID] [varchar](5) NOT NULL,
	[EmployeeName] [varchar](50) NULL,
	[RealName] [varchar](50) NOT NULL,
	[FirstName] [varchar](20) NULL,
	[MidName] [varchar](20) NULL,
	[LastName] [varchar](20) NULL,
	[CF_Active] [bit] NULL,
	[NickName] [varchar](10) NULL,
	[SexCode] [varchar](2) NULL,
	[BloodGroup] [varchar](2) NULL,
	[BirthPlace] [varchar](20) NULL,
	[BirthDate] [datetime] NULL,
	[Pisat] [varchar](5) NULL,
	[KK_No] [varchar](30) NULL,
	[KITAS_No] [varchar](30) NULL,
	[KITAS_ExpDate] [datetime] NULL,
	[KTP_No] [varchar](30) NULL,
	[KTP_ExpDate] [datetime] NULL,
	[SIM_No] [varchar](30) NULL,
	[SIM_ExpDate] [datetime] NULL,
	[Pasport_No] [varchar](30) NULL,
	[PasporName] [varchar](30) NULL,
	[Pasport_ExpDate] [datetime] NULL,
	[DPA_No] [varchar](20) NULL,
	[DPADate] [datetime] NULL,
	[ASTEK_No] [varchar](20) NULL,
	[NPWP_No] [varchar](20) NULL,
	[ReligionCode] [varchar](3) NULL,
	[Citizenship] [varchar](20) NULL,
	[FaskesCodeTk1] [varchar](10) NULL,
	[FaskesNameTk1] [varchar](30) NULL,
	[FaskesCode_DokGigi] [varchar](10) NULL,
	[FaskesName_DokGigi] [varchar](30) NULL,
	[Kelas_Rawat] [smallint] NULL,
	[Polis_Number] [varchar](10) NULL,
	[Polis_Name] [varchar](30) NULL,
	[Address_Line_1] [varchar](255) NULL,
	[Address_VillageCode_1] [varchar](10) NULL,
	[Address_Village_1] [varchar](20) NULL,
	[Address_SubDistrictCode_1] [varchar](10) NULL,
	[Address_SubDistrict_1] [varchar](20) NULL,
	[Address_RT_1] [varchar](20) NULL,
	[Address_RW_1] [varchar](20) NULL,
	[Address_City_1] [varchar](20) NULL,
	[Address_Province_1] [varchar](25) NULL,
	[Address_Area_1] [varchar](25) NULL,
	[Address_Remark_1] [varchar](50) NULL,
	[Address_ZIP_1] [varchar](10) NULL,
	[Address_Phone_1] [varchar](15) NULL,
	[Address_Status_1] [varchar](15) NULL,
	[CF_Flooded] [bit] NULL,
	[CF_PotentialFlooding] [bit] NULL,
	[Address_Line_2] [varchar](255) NULL,
	[Address_VillageCode_2] [varchar](10) NULL,
	[Address_Village_2] [varchar](20) NULL,
	[Address_SubDistrictCode_2] [varchar](10) NULL,
	[Address_SubDistrict_2] [varchar](20) NULL,
	[Address_RT_2] [varchar](20) NULL,
	[Address_RW_2] [varchar](20) NULL,
	[Address_City_2] [varchar](20) NULL,
	[Address_Province_2] [varchar](25) NULL,
	[Address_Remark_2] [varchar](50) NULL,
	[Address_ZIP_2] [varchar](10) NULL,
	[Address_Phone_2] [varchar](15) NULL,
	[Address_Line_3] [varchar](255) NULL,
	[Address_VillageCode_3] [varchar](10) NULL,
	[Address_Village_3] [varchar](20) NULL,
	[Address_SubDistrictCode_3] [varchar](10) NULL,
	[Address_SubDistrict_3] [varchar](20) NULL,
	[Address_RT_3] [varchar](20) NULL,
	[Address_RW_3] [varchar](20) NULL,
	[Address_City_3] [varchar](20) NULL,
	[Address_Province_3] [varchar](25) NULL,
	[Address_Area_3] [varchar](25) NULL,
	[Address_Remark_3] [varchar](50) NULL,
	[Address_ZIP_3] [varchar](10) NULL,
	[Address_Phone_3] [varchar](15) NULL,
	[Address_Status_3] [varchar](15) NULL,
	[MobilePhone] [varchar](15) NULL,
	[Telephone] [varchar](15) NULL,
	[Email] [varchar](50) NULL,
	[EmailOffice] [varchar](100) NULL,
	[MaritalStatus] [varchar](2) NULL,
	[MaritalTaxStatus] [varchar](2) NULL,
	[MaritalTaxUpdate] [datetime] NULL,
	[MarriedDate] [datetime] NULL,
	[EmployeeStatus] [varchar](3) NULL,
	[CompanyName] [varchar](15) NULL,
	[TMT_Kerja] [varchar](20) NULL,
	[JoinDate_Company] [datetime] NULL,
	[JoinDate_ASTRA] [datetime] NULL,
	[PensionDate] [datetime] NULL,
	[ResignDate] [datetime] NULL,
	[ResignType] [varchar](5) NULL,
	[ResignDate_Payroll] [datetime] NULL,
	[ResignReason] [varchar](100) NULL,
	[DeptCode] [varchar](8) NULL,
	[DeptName] [varchar](35) NULL,
	[SectionCode] [varchar](8) NULL,
	[SectionName] [varchar](35) NULL,
	[GroupProcess] [varchar](35) NULL,
	[LevelCode] [varchar](3) NULL,
	[LevelName] [varchar](15) NULL,
	[PositionCode] [varchar](3) NULL,
	[PositionName] [varchar](40) NULL,
	[TitleCode] [varchar](3) NULL,
	[TitleName] [varchar](35) NULL,
	[JobCode] [varchar](10) NULL,
	[StructureClassID] [varchar](10) NULL,
	[StdMaxOVT] [int] NULL,
	[EmplGroup] [varchar](6) NULL,
	[CC_CodeCC] [varchar](6) NULL,
	[CC_CodeDept] [varchar](6) NULL,
	[Grade] [varchar](3) NULL,
	[Bank] [varchar](50) NULL,
	[BankBranch] [varchar](255) NULL,
	[AccountNumber] [varchar](50) NULL,
	[AccountName] [varchar](50) NULL,
	[Father_Name] [varchar](30) NULL,
	[Mother_Name] [varchar](30) NULL,
	[ContactPerson_1] [varchar](30) NULL,
	[ContactAddress_1] [varchar](255) NULL,
	[ContactAddress_City_1] [varchar](20) NULL,
	[ContactAddress_Province_1] [varchar](25) NULL,
	[ContactAddress_ZIP_1] [varchar](10) NULL,
	[ContactAddress_Phone_1] [varchar](15) NULL,
	[ContactStatus_1] [varchar](3) NULL,
	[ContactRelation_1] [varchar](15) NULL,
	[ContactPerson_2] [varchar](30) NULL,
	[ContactAddress_2] [varchar](255) NULL,
	[ContactAddress_City_2] [varchar](20) NULL,
	[ContactAddress_ZIP_2] [varchar](10) NULL,
	[ContactAddress_Province_2] [varchar](25) NULL,
	[ContactAddress_Phone_2] [varchar](15) NULL,
	[ContactStatus_2] [varchar](3) NULL,
	[ContactRelation_2] [varchar](15) NULL,
	[AppraisalGroup] [varchar](6) NULL,
	[AppraisalGroupName] [varchar](20) NULL,
	[ApplicantID] [varchar](7) NULL,
	[PsychoTest_Consultant] [varchar](50) NULL,
	[ForemanID] [varchar](5) NULL,
	[SupervisorID] [varchar](5) NULL,
	[SectionHeadID] [varchar](5) NULL,
	[DeptHeadID] [varchar](5) NULL,
	[PicFile_1] [varchar](50) NULL,
	[PicFile_2] [varchar](50) NULL,
	[PicFile_3] [varchar](50) NULL,
	[PicFile_4] [varchar](50) NULL,
	[PicThumb] [image] NULL,
	[SubCont] [varchar](10) NULL,
	[CF_UserOnly] [bit] NULL,
	[CF_Registered] [bit] NULL,
	[CF_Approved] [bit] NULL,
	[CF_Posted] [bit] NULL,
	[CreatedBy] [varchar](10) NULL,
	[CreatedDate] [datetime] NULL,
	[ApprovedBy] [varchar](10) NULL,
	[ApprovedDate] [datetime] NULL,
	[ModifiedBy] [varchar](10) NULL,
	[ModifiedDate] [datetime] NULL,
	[PostedBy] [varchar](10) NULL,
	[PostedDate] [datetime] NULL,
	[iEmpID] [int] NULL,
	[iBosEmpId] [int] NULL,
	[iOTAdminId] [int] NULL,
	[RecId] [bigint] IDENTITY(1,1) NOT NULL,
	[CF_KopTakaoka] [bit] NULL,
	[JoinDate_KopTakaoka] [datetime] NULL,
	[KopTakaokaID] [varchar](10) NULL,
	[KopTakaokaNumber] [int] NULL,
	[CF_Union] [bit] NULL,
	[JoinDate_Union] [datetime] NULL,
	[WorkingArea] [varchar](20) NULL,
	[Addr1Old] [varchar](255) NULL,
	[CF_C12] [bit] NULL,
	[CF_C2F] [bit] NULL,
	[Remark_C1] [varchar](100) NULL,
	[Remark_C2] [varchar](100) NULL,
	[Remark_CF] [varchar](100) NULL,
	[EFIN_No] [varchar](30) NULL,
	[vBosEmpID] [varchar](6) NULL,
	[BPJSKes_No] [varchar](20) NULL,
	[NOE_StartPeriod] [datetime] NULL,
	[NOE_EndPeriod] [datetime] NULL,
	[DivisionCode] [varchar](8) NULL,
	[DivisionName] [varchar](35) NULL,
	[DivisionHeadID] [varchar](5) NULL,
	[JoinDate_ServiceYear] [datetime] NULL,
	[CF_Ibadah] [bit] NULL,
	[NamaIbadah] [varchar](30) NULL,
	[IbadahDate] [datetime] NULL,
	[RFID_No] [varchar](50) NULL,
 CONSTRAINT [PK_HRD_EMPLOYEE_GENERAL] PRIMARY KEY CLUSTERED 
(
	[EmpID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, FILLFACTOR = 90, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [IX_HRD_EMPLOYEE_TABLE] UNIQUE NONCLUSTERED 
(
	[iEmpID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, FILLFACTOR = 90, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[hydrant_abnormal_cases]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[hydrant_abnormal_cases](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[hydrant_id] [bigint] NULL,
	[inspection_id] [bigint] NULL,
	[abnormal_case] [nvarchar](255) NOT NULL,
	[countermeasure] [nvarchar](max) NULL,
	[due_date] [date] NULL,
	[pic_id] [bigint] NULL,
	[status] [nvarchar](255) NOT NULL,
	[repair_photo] [nvarchar](255) NULL,
	[verified_at] [datetime] NULL,
	[verified_by] [bigint] NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[hydrant_repairs]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[hydrant_repairs](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[hydrant_id] [bigint] NOT NULL,
	[user_id] [varchar](5) NULL,
	[due_date] [date] NULL,
	[description] [nvarchar](max) NOT NULL,
	[status_after_repair] [nvarchar](255) NOT NULL,
	[is_approved] [bit] NOT NULL,
	[photo] [nvarchar](255) NULL,
	[notes] [nvarchar](max) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[progress] [int] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[hydrants]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[hydrants](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[last_inspection_date] [datetime] NULL,
	[status] [nvarchar](255) NOT NULL,
	[code] [nvarchar](255) NOT NULL,
	[location] [nvarchar](255) NULL,
	[area] [nvarchar](255) NULL,
	[x_coordinate] [decimal](8, 2) NULL,
	[y_coordinate] [decimal](8, 2) NULL,
	[type] [nvarchar](255) NULL,
	[is_active] [bit] NOT NULL,
	[weight] [varchar](50) NULL,
	[expired_date] [datetime] NULL,
	[pic_empid] [varchar](10) NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[job_batches]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[job_batches](
	[id] [nvarchar](255) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[total_jobs] [int] NOT NULL,
	[pending_jobs] [int] NOT NULL,
	[failed_jobs] [int] NOT NULL,
	[failed_job_ids] [nvarchar](max) NOT NULL,
	[options] [nvarchar](max) NULL,
	[cancelled_at] [int] NULL,
	[created_at] [int] NOT NULL,
	[finished_at] [int] NULL,
 CONSTRAINT [job_batches_id_primary] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[jobs]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[jobs](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[queue] [nvarchar](255) NOT NULL,
	[payload] [nvarchar](max) NOT NULL,
	[attempts] [tinyint] NOT NULL,
	[reserved_at] [int] NULL,
	[available_at] [int] NOT NULL,
	[created_at] [int] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[migrations]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[migrations](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[migration] [nvarchar](255) NOT NULL,
	[batch] [int] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[password_reset_tokens]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[password_reset_tokens](
	[email] [nvarchar](255) NOT NULL,
	[token] [nvarchar](255) NOT NULL,
	[created_at] [datetime] NULL,
 CONSTRAINT [password_reset_tokens_email_primary] PRIMARY KEY CLUSTERED 
(
	[email] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[sessions]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[sessions](
	[id] [nvarchar](255) NOT NULL,
	[user_id] [varchar](5) NULL,
	[ip_address] [nvarchar](45) NULL,
	[user_agent] [nvarchar](max) NULL,
	[payload] [nvarchar](max) NOT NULL,
	[last_activity] [int] NOT NULL,
 CONSTRAINT [sessions_id_primary] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[user_pic_locations]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[user_pic_locations](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[EMPID] [varchar](5) NOT NULL,
	[device_type] [varchar](10) NOT NULL,
	[location_name] [varchar](50) NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [UK_user_pic_locations] UNIQUE NONCLUSTERED 
(
	[EMPID] ASC,
	[device_type] ASC,
	[location_name] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[users]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[users](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[npk] [nvarchar](255) NOT NULL,
	[password] [nvarchar](255) NOT NULL,
	[remember_token] [nvarchar](100) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[role] [nvarchar](255) NOT NULL,
	[photo] [nvarchar](255) NULL,
	[is_active] [bit] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [Users].[UserTable]    Script Date: 08/04/2026 06:53:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [Users].[UserTable](
	[EMPID] [varchar](5) NOT NULL,
	[USERID] [varchar](15) NOT NULL,
	[REALNAME] [varchar](50) NULL,
	[NICKNAME] [varchar](20) NULL,
	[PASSWD] [varchar](255) NULL,
	[GROUPUSER] [varchar](15) NULL,
	[GROUPMODULE] [varchar](10) NULL,
	[PIC] [image] NULL,
	[PicFile] [varchar](50) NULL,
	[CF_Active] [bit] NULL,
	[LastChangePW] [datetime] NULL,
	[CreatedDate] [datetime] NULL,
	[CreatedBy] [varchar](10) NULL,
	[ModifiedDate] [datetime] NULL,
	[ModifiedBy] [varchar](10) NULL,
	[RecID] [bigint] IDENTITY(1,1) NOT NULL,
	[PWBCK] [varchar](255) NULL,
	[PASSWORD] [varchar](255) NULL,
	[pic_apar_location] [nvarchar](max) NULL,
	[pic_hydrant_location] [nvarchar](max) NULL,
 CONSTRAINT [PK_T_USER] PRIMARY KEY CLUSTERED 
(
	[EMPID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, FILLFACTOR = 90, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [idx_apars_area]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_apars_area] ON [dbo].[apars]
(
	[area] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [idx_apars_area_status]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_apars_area_status] ON [dbo].[apars]
(
	[area] ASC,
	[status] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [idx_apars_code]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_apars_code] ON [dbo].[apars]
(
	[code] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
/****** Object:  Index [idx_apars_expired_date]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_apars_expired_date] ON [dbo].[apars]
(
	[expired_date] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
/****** Object:  Index [idx_apars_last_inspection_date]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_apars_last_inspection_date] ON [dbo].[apars]
(
	[last_inspection_date] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [idx_apars_status]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_apars_status] ON [dbo].[apars]
(
	[status] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
/****** Object:  Index [idx_bai_apar_date]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_bai_apar_date] ON [dbo].[bimonthly_apar_inspections]
(
	[apar_id] ASC,
	[inspection_date] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
/****** Object:  Index [idx_bai_inspection_date]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_bai_inspection_date] ON [dbo].[bimonthly_apar_inspections]
(
	[inspection_date] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
/****** Object:  Index [idx_bhi_hydrant_date]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_bhi_hydrant_date] ON [dbo].[bimonthly_hydrant_inspections]
(
	[hydrant_id] ASC,
	[inspection_date] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
/****** Object:  Index [idx_bhi_inspection_date]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_bhi_inspection_date] ON [dbo].[bimonthly_hydrant_inspections]
(
	[inspection_date] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
/****** Object:  Index [cache_expiration_index]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [cache_expiration_index] ON [dbo].[cache]
(
	[expiration] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
/****** Object:  Index [cache_locks_expiration_index]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [cache_locks_expiration_index] ON [dbo].[cache_locks]
(
	[expiration] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [failed_jobs_uuid_unique]    Script Date: 08/04/2026 06:53:20 ******/
CREATE UNIQUE NONCLUSTERED INDEX [failed_jobs_uuid_unique] ON [dbo].[failed_jobs]
(
	[uuid] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [hydrants_code_unique]    Script Date: 08/04/2026 06:53:20 ******/
CREATE UNIQUE NONCLUSTERED INDEX [hydrants_code_unique] ON [dbo].[hydrants]
(
	[code] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [idx_hydrants_area]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_hydrants_area] ON [dbo].[hydrants]
(
	[area] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [idx_hydrants_area_status]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_hydrants_area_status] ON [dbo].[hydrants]
(
	[area] ASC,
	[status] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [idx_hydrants_code]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_hydrants_code] ON [dbo].[hydrants]
(
	[code] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
/****** Object:  Index [idx_hydrants_last_inspection_date]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_hydrants_last_inspection_date] ON [dbo].[hydrants]
(
	[last_inspection_date] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [idx_hydrants_status]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [idx_hydrants_status] ON [dbo].[hydrants]
(
	[status] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [jobs_queue_index]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [jobs_queue_index] ON [dbo].[jobs]
(
	[queue] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
/****** Object:  Index [sessions_last_activity_index]    Script Date: 08/04/2026 06:53:20 ******/
CREATE NONCLUSTERED INDEX [sessions_last_activity_index] ON [dbo].[sessions]
(
	[last_activity] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
ALTER TABLE [dbo].[apar_abnormal_cases] ADD  DEFAULT ('Open') FOR [status]
GO
ALTER TABLE [dbo].[apar_repairs] ADD  DEFAULT ('OK') FOR [status_after_repair]
GO
ALTER TABLE [dbo].[apar_repairs] ADD  DEFAULT ('0') FOR [is_approved]
GO
ALTER TABLE [dbo].[apar_repairs] ADD  DEFAULT ('0') FOR [progress]
GO
ALTER TABLE [dbo].[apars] ADD  DEFAULT ('ACE') FOR [area]
GO
ALTER TABLE [dbo].[apars] ADD  DEFAULT ('OK') FOR [status]
GO
ALTER TABLE [dbo].[apars] ADD  DEFAULT ((1)) FOR [is_active]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [exp_date_ok]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [pressure_ok]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [weight_co2_ok]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [tube_ok]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [hose_ok]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [bracket_ok]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [wi_ok]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [form_kejadian_ok]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [sign_box_ok]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [sign_triangle_ok]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [marking_tiger_ok]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [marking_beam_ok]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [sr_apar_ok]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [kocok_apar_ok]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] ADD  DEFAULT ('1') FOR [label_ok]
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections] ADD  DEFAULT ('1') FOR [body_hydrant_ok]
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections] ADD  DEFAULT ('1') FOR [selang_ok]
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections] ADD  DEFAULT ('1') FOR [couple_join_ok]
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections] ADD  DEFAULT ('1') FOR [nozzle_ok]
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections] ADD  DEFAULT ('1') FOR [check_sheet_ok]
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections] ADD  DEFAULT ('1') FOR [valve_kran_ok]
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections] ADD  DEFAULT ('1') FOR [lampu_ok]
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections] ADD  DEFAULT ('1') FOR [cover_lampu_ok]
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections] ADD  DEFAULT ('1') FOR [box_display_ok]
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections] ADD  DEFAULT ('1') FOR [konsul_hydrant_ok]
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections] ADD  DEFAULT ('1') FOR [jr_ok]
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections] ADD  DEFAULT ('1') FOR [marking_ok]
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections] ADD  DEFAULT ('1') FOR [label_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [exp_date_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [pressure_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [weight_co2_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [tube_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [hose_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [bracket_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [wi_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [form_kejadian_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [sign_box_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [sign_triangle_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [marking_tiger_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [marking_beam_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [sr_apar_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [kocok_apar_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [body_hydrant_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [selang_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [couple_join_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [nozzle_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [check_sheet_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [valve_kran_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [lampu_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [cover_lampu_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [box_display_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [konsul_hydrant_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [jr_ok]
GO
ALTER TABLE [dbo].[bimonthly_inspections] ADD  DEFAULT ('1') FOR [marking_ok]
GO
ALTER TABLE [dbo].[failed_jobs] ADD  DEFAULT (getdate()) FOR [failed_at]
GO
ALTER TABLE [dbo].[HRD_EMPLOYEE_TABLE] ADD  CONSTRAINT [DF_HRD_EMPLOYEE_TABLE_JobCode]  DEFAULT ((56)) FOR [JobCode]
GO
ALTER TABLE [dbo].[HRD_EMPLOYEE_TABLE] ADD  CONSTRAINT [DF_HRD_EMPLOYEE_TABLE_StdMaxOVT]  DEFAULT ((56)) FOR [StdMaxOVT]
GO
ALTER TABLE [dbo].[hydrant_repairs] ADD  DEFAULT ('Good') FOR [status_after_repair]
GO
ALTER TABLE [dbo].[hydrant_repairs] ADD  DEFAULT ('0') FOR [is_approved]
GO
ALTER TABLE [dbo].[hydrant_repairs] ADD  DEFAULT ('0') FOR [progress]
GO
ALTER TABLE [dbo].[hydrants] ADD  DEFAULT ('Good') FOR [status]
GO
ALTER TABLE [dbo].[hydrants] ADD  DEFAULT ((1)) FOR [is_active]
GO
ALTER TABLE [dbo].[users] ADD  DEFAULT ('Petugas') FOR [role]
GO
ALTER TABLE [dbo].[apar_abnormal_cases]  WITH CHECK ADD  CONSTRAINT [apar_abnormal_cases_apar_id_foreign] FOREIGN KEY([apar_id])
REFERENCES [dbo].[apars] ([id])
ON DELETE SET NULL
GO
ALTER TABLE [dbo].[apar_abnormal_cases] CHECK CONSTRAINT [apar_abnormal_cases_apar_id_foreign]
GO
ALTER TABLE [dbo].[apar_abnormal_cases]  WITH CHECK ADD  CONSTRAINT [apar_abnormal_cases_inspection_id_foreign] FOREIGN KEY([inspection_id])
REFERENCES [dbo].[bimonthly_apar_inspections] ([id])
GO
ALTER TABLE [dbo].[apar_abnormal_cases] CHECK CONSTRAINT [apar_abnormal_cases_inspection_id_foreign]
GO
ALTER TABLE [dbo].[apar_abnormal_cases]  WITH CHECK ADD  CONSTRAINT [apar_abnormal_cases_pic_id_foreign] FOREIGN KEY([pic_id])
REFERENCES [dbo].[users] ([id])
GO
ALTER TABLE [dbo].[apar_abnormal_cases] CHECK CONSTRAINT [apar_abnormal_cases_pic_id_foreign]
GO
ALTER TABLE [dbo].[apar_abnormal_cases]  WITH CHECK ADD  CONSTRAINT [apar_abnormal_cases_verified_by_foreign] FOREIGN KEY([verified_by])
REFERENCES [dbo].[users] ([id])
GO
ALTER TABLE [dbo].[apar_abnormal_cases] CHECK CONSTRAINT [apar_abnormal_cases_verified_by_foreign]
GO
ALTER TABLE [dbo].[apar_repairs]  WITH CHECK ADD  CONSTRAINT [apar_repairs_apar_id_foreign] FOREIGN KEY([apar_id])
REFERENCES [dbo].[apars] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[apar_repairs] CHECK CONSTRAINT [apar_repairs_apar_id_foreign]
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections]  WITH CHECK ADD  CONSTRAINT [bimonthly_apar_inspections_apar_id_foreign] FOREIGN KEY([apar_id])
REFERENCES [dbo].[apars] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[bimonthly_apar_inspections] CHECK CONSTRAINT [bimonthly_apar_inspections_apar_id_foreign]
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections]  WITH CHECK ADD  CONSTRAINT [bimonthly_hydrant_inspections_hydrant_id_foreign] FOREIGN KEY([hydrant_id])
REFERENCES [dbo].[hydrants] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[bimonthly_hydrant_inspections] CHECK CONSTRAINT [bimonthly_hydrant_inspections_hydrant_id_foreign]
GO
ALTER TABLE [dbo].[bimonthly_inspections]  WITH CHECK ADD  CONSTRAINT [bimonthly_inspections_apar_unit_id_foreign] FOREIGN KEY([apar_unit_id])
REFERENCES [dbo].[apars] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[bimonthly_inspections] CHECK CONSTRAINT [bimonthly_inspections_apar_unit_id_foreign]
GO
ALTER TABLE [dbo].[bimonthly_inspections]  WITH CHECK ADD  CONSTRAINT [bimonthly_inspections_hydrant_unit_id_foreign] FOREIGN KEY([hydrant_unit_id])
REFERENCES [dbo].[hydrants] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[bimonthly_inspections] CHECK CONSTRAINT [bimonthly_inspections_hydrant_unit_id_foreign]
GO
ALTER TABLE [dbo].[hydrant_abnormal_cases]  WITH CHECK ADD  CONSTRAINT [hydrant_abnormal_cases_hydrant_id_foreign] FOREIGN KEY([hydrant_id])
REFERENCES [dbo].[hydrants] ([id])
ON DELETE SET NULL
GO
ALTER TABLE [dbo].[hydrant_abnormal_cases] CHECK CONSTRAINT [hydrant_abnormal_cases_hydrant_id_foreign]
GO
ALTER TABLE [dbo].[hydrant_abnormal_cases]  WITH CHECK ADD  CONSTRAINT [hydrant_abnormal_cases_inspection_id_foreign] FOREIGN KEY([inspection_id])
REFERENCES [dbo].[bimonthly_hydrant_inspections] ([id])
GO
ALTER TABLE [dbo].[hydrant_abnormal_cases] CHECK CONSTRAINT [hydrant_abnormal_cases_inspection_id_foreign]
GO
ALTER TABLE [dbo].[hydrant_abnormal_cases]  WITH CHECK ADD  CONSTRAINT [hydrant_abnormal_cases_pic_id_foreign] FOREIGN KEY([pic_id])
REFERENCES [dbo].[users] ([id])
GO
ALTER TABLE [dbo].[hydrant_abnormal_cases] CHECK CONSTRAINT [hydrant_abnormal_cases_pic_id_foreign]
GO
ALTER TABLE [dbo].[hydrant_abnormal_cases]  WITH CHECK ADD  CONSTRAINT [hydrant_abnormal_cases_verified_by_foreign] FOREIGN KEY([verified_by])
REFERENCES [dbo].[users] ([id])
GO
ALTER TABLE [dbo].[hydrant_abnormal_cases] CHECK CONSTRAINT [hydrant_abnormal_cases_verified_by_foreign]
GO
ALTER TABLE [dbo].[hydrant_repairs]  WITH CHECK ADD  CONSTRAINT [hydrant_repairs_hydrant_id_foreign] FOREIGN KEY([hydrant_id])
REFERENCES [dbo].[hydrants] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[hydrant_repairs] CHECK CONSTRAINT [hydrant_repairs_hydrant_id_foreign]
GO
ALTER TABLE [dbo].[apar_abnormal_cases]  WITH CHECK ADD  CONSTRAINT [ck_apar_abnormal_cases_status] CHECK  (([status]='Verified' OR [status]='Closed' OR [status]='On Progress' OR [status]='Open'))
GO
ALTER TABLE [dbo].[apar_abnormal_cases] CHECK CONSTRAINT [ck_apar_abnormal_cases_status]
GO
ALTER TABLE [dbo].[apars]  WITH CHECK ADD  CONSTRAINT [ck_apars_area] CHECK  (([area]='Office' OR [area]='Disa' OR [area]='Machining' OR [area]='ACE'))
GO
ALTER TABLE [dbo].[apars] CHECK CONSTRAINT [ck_apars_area]
GO
ALTER TABLE [dbo].[apars]  WITH CHECK ADD  CONSTRAINT [ck_apars_status] CHECK  (([status]='NG' OR [status]='OK'))
GO
ALTER TABLE [dbo].[apars] CHECK CONSTRAINT [ck_apars_status]
GO
ALTER TABLE [dbo].[HRD_EMPLOYEE_TABLE]  WITH NOCHECK ADD  CONSTRAINT [CK_HRD_EMPLOYEE_TABLE] CHECK  (([EmpID]<>''))
GO
ALTER TABLE [dbo].[HRD_EMPLOYEE_TABLE] CHECK CONSTRAINT [CK_HRD_EMPLOYEE_TABLE]
GO
ALTER TABLE [dbo].[users]  WITH CHECK ADD  CONSTRAINT [ck_users_role] CHECK  (([role]='Petugas' OR [role]='Admin'))
GO
ALTER TABLE [dbo].[users] CHECK CONSTRAINT [ck_users_role]
GO
USE [master]
GO
ALTER DATABASE [ATI] SET  READ_WRITE 
GO


